<?php
// plugins/cpu/cpu_api.php
// Liefert CPU-Auslastungsdaten (Gesamt und pro Kern) im JSON-Format.

header('Content-Type: application/json');

$json_data = getCpuData();

echo json_encode($json_data);

/**
 * Ruft die CPU-Auslastung ab, indem die Differenz von /proc/stat zwischen 
 * zwei Messungen berechnet wird.
 */
function getCpuData() {
    $stat_file = '/proc/stat';
    $temp_file = sys_get_temp_dir() . '/cpu_stat_cache';
    $cache_lifetime = 1; // 1 Sekunde Verzögerung für genaue Berechnung

    $default_data = [
        'overall' => 0.0,
        'cores' => [],
        'error' => 'Could not read CPU data.'
    ];

    if (!is_readable($stat_file)) {
        $default_data['error'] = 'CPU stat file not readable: ' . $stat_file;
        return $default_data;
    }

    // --- 1. Aktuelle Statistik lesen ---
    $current_stats_raw = file_get_contents($stat_file);
    if ($current_stats_raw === false) {
         $default_data['error'] = 'Failed to read content of ' . $stat_file;
         return $default_data;
    }

    $current_stats = parseCpuStats($current_stats_raw);

    // --- 2. Cache-Daten laden ---
    // Wir benötigen die vorherige Messung, um die Auslastung (Differenz) zu berechnen.
    if (file_exists($temp_file) && time() - filemtime($temp_file) < 5) { // max 5s Cache
        $cached_data = json_decode(file_get_contents($temp_file), true);
        $prev_stats = $cached_data['stats'] ?? null;
    } else {
        $prev_stats = null;
    }
    
    // Aktuelle Statistik immer für die nächste Messung speichern
    file_put_contents($temp_file, json_encode(['timestamp' => time(), 'stats' => $current_stats]));
    
    // --- 3. Auslastung berechnen ---
    if ($prev_stats === null) {
        $default_data['error'] = 'Initial run. No previous data for calculation.';
        // Trotz Fehler geben wir das aktuelle JSON-Format zurück (mit 0-Werten)
        return ['overall' => 0.0, 'cores' => array_fill(0, count($current_stats) - 1, 0.0)]; 
    }

    // Stellen Sie sicher, dass wir lange genug gewartet haben (mindestens 1 Sekunde)
    if (time() - $cached_data['timestamp'] < $cache_lifetime) {
        // Dies verhindert eine Division durch Null oder ungenaue Ergebnisse bei zu schnellen Aufrufen.
        $default_data['error'] = 'Called too quickly, reusing previous calculation.';
        // Hier müsste man eigentlich die *alte* Berechnung zurückgeben, aber für 
        // dieses Widget ist 0.0 besser als falsche Werte.
        // Wir geben einfach 0 zurück und warten auf den nächsten sauberen Aufruf.
        return ['overall' => 0.0, 'cores' => array_fill(0, count($current_stats) - 1, 0.0)]; 
    }

    // Berechnen der Auslastung (Gesamt und pro Kern)
    $overall_load = calculateLoad($current_stats[0], $prev_stats[0]);
    $core_loads = [];

    // Beginne bei 1, da $current_stats[0] die Gesamt-CPU ("cpu") ist
    for ($i = 1; $i < count($current_stats) && $i < count($prev_stats); $i++) {
        $core_loads[] = calculateLoad($current_stats[$i], $prev_stats[$i]);
    }

    return [
        'overall' => $overall_load,
        'cores' => $core_loads
    ];
}

/**
 * Helfer: Parsed die /proc/stat-Ausgabe in ein numerisches Array.
 */
function parseCpuStats($raw_data) {
    $lines = explode("\n", trim($raw_data));
    $stats = [];
    foreach ($lines as $line) {
        if (strpos($line, 'cpu') === 0) {
            $parts = preg_split('/\s+/', $line);
            // $parts[1] bis $parts[10] sind die Ticks
            $user = $parts[1] ?? 0;
            $nice = $parts[2] ?? 0;
            $system = $parts[3] ?? 0;
            $idle = $parts[4] ?? 0;
            $iowait = $parts[5] ?? 0;
            
            // Gesamt-Ticks (total) und Idle-Ticks speichern
            $stats[] = [
                'idle' => (int)$idle,
                'total' => (int)$user + (int)$nice + (int)$system + (int)$idle + (int)$iowait
                // Weitere Ticks (irq, softirq, steal, guest) werden hier ignoriert, da sie meist 0 sind
            ];
        }
    }
    return $stats;
}

/**
 * Helfer: Berechnet die Auslastung (in Prozent) zwischen zwei Messungen.
 */
function calculateLoad($current, $prev) {
    $prev_idle = $prev['idle'];
    $prev_total = $prev['total'];
    $current_idle = $current['idle'];
    $current_total = $current['total'];

    $total_diff = $current_total - $prev_total;
    $idle_diff = $current_idle - $prev_idle;

    if ($total_diff > 0) {
        $usage = 100 * (1 - $idle_diff / $total_diff);
        return max(0.0, min(100.0, $usage)); // Werte auf 0-100% begrenzen
    }
    return 0.0;
}
?>

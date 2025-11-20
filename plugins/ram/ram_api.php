<?php
// plugins/ram/ram_api.php
// Liefert RAM- und Swap-Auslastungsdaten im JSON-Format.

header('Content-Type: application/json');

$json_data = getRamData();

echo json_encode($json_data);

function getRamData() {
    $data = [
        'mem' => [
            'total' => 0,
            'used' => 0,
            'free' => 0,
            'available' => 0,
            'cached' => 0,
            'usage_percent' => 0.0
        ],
        'swap' => [
            'total' => 0,
            'used' => 0,
            'free' => 0,
            'usage_percent' => 0.0
        ],
        'error' => null
    ];

    $meminfo_file = '/proc/meminfo';

    if (!is_readable($meminfo_file)) {
        $data['error'] = 'Datei /proc/meminfo nicht lesbar.';
        return $data;
    }

    $lines = file($meminfo_file);
    $info = [];
    foreach ($lines as $line) {
        if (preg_match('/^(\w+):\s+(\d+)\s+kB/', $line, $matches)) {
            // Speichere die Werte in Byte (kB * 1024)
            $info[$matches[1]] = (int)$matches[2] * 1024; 
        }
    }

    if (empty($info)) {
        $data['error'] = 'Fehler beim Parsen von /proc/meminfo.';
        return $data;
    }

    // Hauptspeicher (RAM)
    $memTotal = $info['MemTotal'] ?? 0;
    $memFree = $info['MemFree'] ?? 0;
    $memAvailable = $info['MemAvailable'] ?? 0;
    $memCached = ($info['Cached'] ?? 0) + ($info['Buffers'] ?? 0); // Cached beinhaltet Buffers

    // Berechnung (logische Nutzung)
    $memUsed = $memTotal - $memAvailable; 
    
    // Berechnung des Prozentsatzes basierend auf 'Available' (für den Balken)
    $usagePercent = ($memTotal > 0) ? ($memUsed / $memTotal) * 100 : 0.0;


    $data['mem']['total'] = $memTotal;
    $data['mem']['used'] = $memUsed;
    $data['mem']['free'] = $memFree;
    $data['mem']['available'] = $memAvailable;
    $data['mem']['cached'] = $memCached;
    $data['mem']['usage_percent'] = round($usagePercent, 1);
    
    // Swap-Speicher
    $swapTotal = $info['SwapTotal'] ?? 0;
    $swapFree = $info['SwapFree'] ?? 0;
    $swapUsed = $swapTotal - $swapFree;
    $swapPercent = ($swapTotal > 0) ? ($swapUsed / $swapTotal) * 100 : 0.0;

    $data['swap']['total'] = $swapTotal;
    $data['swap']['used'] = $swapUsed;
    $data['swap']['free'] = $swapFree;
    $data['swap']['usage_percent'] = round($swapPercent, 1);

    return $data;
}
?>

<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

function getCPULoad() {
    $load = sys_getloadavg();
    $cpuload = $load[0];
    return round($cpuload, 2);
}

function getRAMUsage() {
    $free = shell_exec('free -m');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = preg_split('/\s+/', $free_arr[1]);
    $ram_total = floatval($mem[1]);
    $ram_used = floatval($mem[2]);

    // Überprüfe, ob der Gesamtspeicher null ist, um eine Division durch Null zu verhindern
    if ($ram_total == 0) {
        return 0; // Du kannst einen Wert deiner Wahl zurückgeben
    }

    $ram_usage = ($ram_used / $ram_total) * 100;
    return round($ram_usage, 2);
}

function getSwapUsage() {
    $free = shell_exec('free -m');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $swap = preg_split('/\s+/', $free_arr[2]);
    $swap_total = floatval($swap[1]);
    $swap_used = floatval($swap[2]);

    // Überprüfe, ob der Gesamtspeicher null ist, um eine Division durch Null zu verhindern
    if ($swap_total == 0) {
        return 0; // Du kannst einen Wert deiner Wahl zurückgeben
    }

    $swap_usage = ($swap_used / $swap_total) * 100;
    return round($swap_usage, 2);
}

// Befehl, um die Auslastung der CPU-Kerne zu erhalten
$output = shell_exec('mpstat -P ALL');
$lines = explode("\n", $output);
$coreLoads = array();

// Beginne bei Zeile 4, um die Daten der CPU-Kerne zu extrahieren
for ($i = 4; $i < count($lines) - 1; $i++) {
    $parts = preg_split('/\s+/', $lines[$i]);
    $coreLoad = floatval(str_replace(',', '.', $parts[count($parts) - 1]));
    $coreLoads[] = $coreLoad;
}

$data = [
    'cpuLoad' => getCPULoad(),
    'ramUsage' => getRAMUsage(),
    'swapUsage' => getSwapUsage(),
    'coreLoads' => $coreLoads,
];
header('Content-Type: application/json');
echo json_encode($data);
?>

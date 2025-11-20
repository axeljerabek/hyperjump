<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Befehl, um die Auslastung der CPU-Kerne zu erhalten
$output = shell_exec('mpstat -P ALL 1 1');
$lines = explode("\n", $output);
$usrLoads = array(); // Array für die %usr-Werte

// Beginnen Sie bei Zeile 0, um die Daten der CPU-Kerne zu extrahieren
for ($i = 0; $i < count($lines); $i++) {
    $parts = preg_split('/\s+/', $lines[$i]);
    if (count($parts) >= 3) {
        $usrLoad = str_replace(',', '.', $parts[2]);
        $usrLoads[] = (float) $usrLoad;
    }
}

// Schneiden Sie das Array auf die letzten 8 Werte
$usrLoads = array_slice($usrLoads, -8);

$data = [
    'coreLoads' => $usrLoads,
];

header('Content-Type: application/json');
echo json_encode($data);
?>

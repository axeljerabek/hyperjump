<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Befehl, um die GPU-Informationen zu erhalten
$output = shell_exec('nvidia-smi');
$lines = explode("\n", $output);

// Extrahiere GPU-Utilization (Prozent)
$gpuUtilizationLine = $lines[9]; // Zeile, die die GPU-Utilization enthält
preg_match('/(\d+)%/', $gpuUtilizationLine, $gpuUtilizationMatches);
$gpuUtilization = intval($gpuUtilizationMatches[1]);

// Extrahiere Memory-Usage (belegter Speicher und Gesamtspeicher in MiB)
preg_match_all('/(\d+)MiB/', $output, $memoryUsageMatches);

if (count($memoryUsageMatches[0]) >= 2) {
    $usedMemory = intval($memoryUsageMatches[1][0]);
    $totalMemory = intval($memoryUsageMatches[1][1]);
} else {
    $usedMemory = 0;
    $totalMemory = 0;
}

$data = [
    'gpuUtilization' => $gpuUtilization,
    'memoryUsage' => [
        'used' => $usedMemory,
        'total' => $totalMemory,
    ],
];

header('Content-Type: application/json');
echo json_encode($data);
?>

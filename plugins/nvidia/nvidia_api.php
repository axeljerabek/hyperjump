<?php
// plugins/nvidia/nvidia_api.php
// Ruft GPU-Daten über die Textausgabe von nvidia-smi ab und parst diese.

header('Content-Type: application/json');

// Standard-Daten bei Fehler
$defaultData = [
    'gpuUtilization' => 0, 
    'memoryUsage' => [
        'used' => 0, 
        'total' => 0,
    ],
    'error' => 'NVIDIA-SMI parsing failed.'
];

// Befehl, um die GPU-Informationen zu erhalten
$command = 'nvidia-smi 2>&1'; // Auch Fehler umleiten
$output = shell_exec($command);

// Prüfen, ob die Ausgabe gültige NVIDIA-Daten enthält
if (empty($output) || strpos($output, 'NVIDIA-SMI') === false) {
    $defaultData['error'] = 'NVIDIA-SMI command output empty or invalid. Check PATH/permissions.';
    echo json_encode($defaultData);
    exit;
}

// --- PARSING DER TEXTAUSGABE (Robustere Methode) ---

$gpuUtilization = 0;
$memoryUsed = 0;
$memoryTotal = 0;
$parsingSuccessful = false;

// 1. Suche nach Memory Usage (belegter Speicher und Gesamtspeicher in MiB)
// Muster: (\d+)MiB
preg_match_all('/(\d+)MiB/', $output, $memoryUsageMatches);

if (count($memoryUsageMatches[0]) >= 2) {
    $memoryUsed = (int)$memoryUsageMatches[1][0]; // Erster gefundener MiB-Wert (sollte der belegte Speicher sein)
    $memoryTotal = (int)$memoryUsageMatches[1][1]; // Zweiter gefundener MiB-Wert (sollte der Gesamtspeicher sein)
}

// 2. Suche nach GPU-Utilization (Prozent)
// Suchen wir nach dem Muster, das direkt auf der Zeile mit dem Speicher steht: | XX% Default |
if (preg_match('/\|\s+(\d+)\%\s+Default\s*\|/', $output, $gpuUtilizationMatches)) {
    $gpuUtilization = (float)$gpuUtilizationMatches[1];
    $parsingSuccessful = true;
} else if (preg_match('/\|\s+(\d+)\%\s+Default\s+\|/', $output, $gpuUtilizationMatches)) {
    // Fallback für leicht abweichendes Leerzeichen-Muster
    $gpuUtilization = (float)$gpuUtilizationMatches[1];
    $parsingSuccessful = true;
}


if (!$parsingSuccessful && $memoryTotal === 0) {
    $defaultData['error'] = 'NVIDIA-SMI text parsing failed (Regex mismatch).';
    echo json_encode($defaultData);
    exit;
}


// Ausgabe des Ergebnisses
echo json_encode([
    'gpuUtilization' => $gpuUtilization,
    'memoryUsage' => [
        'used' => $memoryUsed,
        'total' => $memoryTotal
    ]
]);
?>

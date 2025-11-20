<?php
// plugins/network/network_widget.php
// Berechnet die Netzwerk-Traffic-Rate (RX/TX in KB/s) für die definierten Schnittstellen.

header('Content-Type: application/json');

// Definieren Sie die Schnittstellen, die überwacht werden sollen
// PASSEN SIE DIESE LISTE AN IHRE AKTUELLE SYSTEMKONFIGURATION AN!
$interfaces_to_monitor = ['enp89s0', 'wlp0s20f3'];

// WICHTIG: Pfad zur temporären Cache-Datei
// Wir verwenden den ursprünglichen Pfad /tmp/, da er oft bessere Schreibberechtigungen hat
$temp_file = '/tmp/sysinfo_net_stats_cache.json';

/**
 * Holt die aktuellen RX- und TX-Bytes für die definierten Netzwerkschnittstellen.
 * @param array $interfaces Die zu überwachenden Schnittstellen.
 * @return array Enthält 'rx_bytes' und 'tx_bytes' (Gesamt).
 */
function get_net_stats(array $interfaces) {
    if (!file_exists('/proc/net/dev') || !is_readable('/proc/net/dev')) {
        return ['rx_bytes' => 0, 'tx_bytes' => 0];
    }

    $total_rx_bytes = 0;
    $total_tx_bytes = 0;
    $lines = @file('/proc/net/dev'); // @ unterdrückt Fehler, falls Datei nicht lesbar

    if ($lines === false) {
        return ['rx_bytes' => 0, 'tx_bytes' => 0];
    }

    foreach ($lines as $line) {
        // Bereinigen der Zeile von führenden/abschließenden Leerzeichen
        $line = trim($line);

        // Überprüfen, ob die Zeile eine der überwachten Schnittstellen enthält
        foreach ($interfaces as $interface) {

            // Muster: Schnittstellenname: RX_bytes ... (8. Feld) ... TX_bytes
            if (preg_match('/^' . preg_quote($interface) . ':\s*(\d+)\s*\d+\s*\d+\s*\d+\s*\d+\s*\d+\s*\d+\s*\d+\s*(\d+)/', $line, $matches)) {

                // $matches[1] ist RX_bytes
                // $matches[2] ist TX_bytes
                if (isset($matches[1]) && isset($matches[2])) {
                    $total_rx_bytes += (float)$matches[1];
                    $total_tx_bytes += (float)$matches[2];

                    // Schnittstelle gefunden und verarbeitet, zur nächsten Zeile
                    break;
                }
            }
        }
    }
    return ['rx_bytes' => $total_rx_bytes, 'tx_bytes' => $total_tx_bytes];
}

// --- HAUPTLOGIK ---
$current_stats = get_net_stats($interfaces_to_monitor);
$rx_rate = 0;
$tx_rate = 0;

if ($current_stats['rx_bytes'] > 0 || $current_stats['tx_bytes'] > 0) {

    if (file_exists($temp_file) && is_readable($temp_file)) {
        $previous_data = @json_decode(file_get_contents($temp_file), true);

        if ($previous_data && isset($previous_data['timestamp']) && isset($previous_data['stats'])) {
            $time_diff = time() - $previous_data['timestamp'];
            $previous_stats = $previous_data['stats'];

            if ($time_diff >= 0.5) {

                $delta_rx = max(0, $current_stats['rx_bytes'] - $previous_stats['rx_bytes']);
                $delta_tx = max(0, $current_stats['tx_bytes'] - $previous_stats['tx_bytes']);

                // Rate in Bytes/Sekunde
                $rx_rate = $delta_rx / $time_diff;
                $tx_rate = $delta_tx / $time_diff;
            }
        }
    }

    // Speichern der aktuellen Zähler, wenn die Berechtigung besteht
    if (@is_writable(dirname($temp_file)) || (@file_exists($temp_file) && @is_writable($temp_file))) {
        @file_put_contents($temp_file, json_encode([
            'timestamp' => time(),
            'stats' => $current_stats
        ]));
    }
}

// JSON-Ausgabe (Bytes/Sekunde konvertiert zu KB/Sekunde)
echo json_encode([
    'rx_rate_kbps' => round(max(0, $rx_rate / 1024), 2),
    'tx_rate_kbps' => round(max(0, $tx_rate / 1024), 2),
]);
?>

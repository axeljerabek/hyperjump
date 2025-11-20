<?php
/**
 * Kamera-Status Plugin - DYNAMISCHE VERSION
 * Liest alle Status aus camera_status.json und generiert das HTML für die Indikatoren.
 */

// Konfiguration
$status_file = '../../camera_status.json'; 
// HINWEIS: $num_cams wird in dieser dynamischen Version NICHT MEHR benötigt.

// --- Funktionen ---

/**
 * Gibt die CSS-Klasse basierend auf dem Status (0 oder 1) zurück.
 */
function get_status_class($status) {
    return ($status == 1) ? 'status-active' : 'status-idle';
}

// --- Hauptlogik ---

$output_html = '';
$motion_detected = false;
$success = false;
$error_message = '';
$data = [];

// 1. JSON-Datei lesen und decodieren
if (file_exists($status_file)) {
    $json_data = file_get_contents($status_file);
    if ($json_data !== false) {
        $data = json_decode($json_data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $success = true;
        } else {
            $error_message = "JSON-Fehler beim Decodieren der Statusdatei.";
        }
    } else {
        $error_message = "Konnte Statusdatei nicht lesen (Prüfe Berechtigungen).";
    }
} else {
    $error_message = "Statusdatei " . basename($status_file) . " nicht gefunden (Pfad prüfen!).";
}


// 2. Statuspunkte generieren (Dynamische Iteration über ALLE Keys)
if ($success && !empty($data)) {
    
    // Optional, aber empfohlen: Sortiert die Keys (cam1, cam101, cam2, cam102...) 
    // numerisch für eine konsistente Anzeigereihenfolge
    ksort($data, SORT_NUMERIC); 

    foreach ($data as $cam_id => $status) {
        
        // Extrahieren der reinen Zahl für das Label (z.B. '104' aus 'cam104')
        // Nur Zahlen beibehalten
        $cam_number = preg_replace('/[^0-9]/', '', $cam_id);

        $class = get_status_class($status);
        $status_text = ($status == 1) ? 'Bewegung erkannt' : 'Inaktiv';
        // Titel im Tooltip verwenden
        $title = "Cam " . $cam_number . " (" . $status_text . ")";
        
        if ($status == 1) {
            $motion_detected = true;
        }

        $output_html .= '
            <div 
                class="indicator-point ' . $class . '" 
                title="' . $title . '"
            >
                <span class="cam-label">C' . $cam_number . '</span>
            </div>
        ';
    }
} elseif (!$success) {
    // Fehler-HTML generieren
    $output_html = '<span class="status-error">' . $error_message . '</span>';
} else {
     // Keine Daten gefunden (JSON leer)
     $output_html = '<span class="status-error">Keine Kamera-Daten gefunden.</span>';
}


// 3. Ausgabe als JSON
header('Content-Type: application/json');
echo json_encode([
    'html' => $output_html,
    'motion_detected' => $motion_detected,
    'success' => $success && !empty($data)
]);
?>

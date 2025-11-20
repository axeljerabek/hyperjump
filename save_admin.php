<?php
include 'config.php'; 

// *** SICHERHEITS-CHECK: Nur eingeloggte Admins dürfen speichern ***
if (!IS_LOGGED_IN) {
    http_response_code(403); 
    header('Location: admin.php?save_status=error');
    exit;
}
// *** Ende SICHERHEITS-CHECK ***

$dataFile = 'data.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_all' && isset($_POST['categories'])) {
    
    $inputCategories = $_POST['categories'];
    $newCategories = [];

    foreach ($inputCategories as $categoryId => $categoryData) {
        
        // NEU: Titel und Farbe werden nun direkt aus den bearbeitbaren Feldern übernommen
        // Die trim() Funktion entfernt unnötige Leerzeichen.
        $newCategory = [
            'title' => trim($categoryData['title']),
            'color' => trim($categoryData['color']),
            'links' => []
        ];

        // Links verarbeiten
        if (isset($categoryData['links']) && is_array($categoryData['links'])) {
            foreach ($categoryData['links'] as $linkData) {
                // Nur Links speichern, die tatsächlich Daten enthalten
                if (empty($linkData['text']) || empty($linkData['url'])) {
                    continue; 
                }

                $newLink = [
                    'url' => trim($linkData['url']),
                    'text' => trim($linkData['text']),
                    'icon' => trim($linkData['icon'] ?? 'link'),
                    // Bubble-Farbe: Nimmt entweder den gesendeten Wert oder einen leeren String
                    'color' => trim($linkData['color'] ?? ''), 
                    // Disabled: Prüft, ob der Checkbox-Wert 'true' gesendet wurde
                    'disabled' => isset($linkData['disabled']) && $linkData['disabled'] === 'true',
                ];
                
                $newCategory['links'][] = $newLink;
            }
        }

        $newCategories[$categoryId] = $newCategory;
    }

    // 1. Array in PHP-Code konvertieren
    // Wir setzen var_export() auf false, damit wir die Rückgabe in eine Variable bekommen
    $newPhpArrayString = var_export($newCategories, true);

    // 2. Den neuen Inhalt für data.php erstellen
    $newContent = "<?php\n\n";
    $newContent .= "// Dieses Array wird automatisch durch das Admin Dashboard generiert.\n";
    $newContent .= "\$categories = " . $newPhpArrayString . ";\n\n";
    $newContent .= "?>";

    // 3. Speichern der aktualisierten data.php mit LOCK_EX
    if (file_put_contents($dataFile, $newContent, LOCK_EX) !== false) {
        // Erfolg
        header('Location: admin.php?save_status=success');
        exit;
    } else {
        // Fehler beim Schreiben
        header('Location: admin.php?save_status=error');
        exit;
    }

} else {
    // Fehler: Ungültiger Request
    header('Location: admin.php?save_status=error');
    exit;
}
?>

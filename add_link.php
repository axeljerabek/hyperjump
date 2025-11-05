<?php
// MUSS VORHER DIE config.php LADEN, um die Session und den Status zu prüfen!
include 'config.php'; 

// *** SICHERHEITS-CHECK: Nur eingeloggte Admins dürfen speichern ***
if (!IS_LOGGED_IN) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Access Denied. You must be logged in to add a link.']);
    exit;
}
// *** Ende SICHERHEITS-CHECK ***

header('Content-Type: application/json');

// 1. Eingabedaten aus dem POST-Request empfangen
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data['text']) || empty($data['url']) || empty($data['category'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing link, URL, or category data.']);
    exit;
}

$linkText = $data['text'];
$linkUrl = $data['url'];
$linkIcon = $data['icon'];
$categoryId = $data['category'];

$dataFile = 'data.php';

// 2. data.php einlesen und den Inhalt als String speichern
if (!file_exists($dataFile)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'data.php not found.']);
    exit;
}

$content = file_get_contents($dataFile);

// 3. Den PHP-Code block extrahieren, der das Array $categories definiert
// Der Regex sucht nach: $categories = [ ... ];
if (!preg_match('/\$categories\s*=\s*(.*?);/s', $content, $matches)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not find $categories array definition in data.php.']);
    exit;
}

$phpArrayString = $matches[1];

// 4. Den String in ein echtes PHP-Array konvertieren
// Wir müssen den String in eine temporäre Datei schreiben und "includen", um ihn zu parsen.
$tempFile = tempnam(sys_get_temp_dir(), 'php_array');
file_put_contents($tempFile, '<?php $temp_categories = ' . $phpArrayString . ';');

// Error-Handling beim Include (könnte zu Fehlern führen, wenn die data.php Syntaxfehler hat)
$temp_categories = [];
try {
    include $tempFile;
} catch (\Throwable $e) {
    // Falls ein Fehler auftritt, wird temp_categories nicht gesetzt oder ist fehlerhaft.
}
unlink($tempFile);

$categories = $temp_categories;

// 5. Den neuen Link hinzufügen
$newLink = [
    'url' => $linkUrl,
    'text' => $linkText,
    'icon' => $linkIcon,
    'disabled' => false
];

if (isset($categories[$categoryId])) {
    // Fügt den neuen Link an den ANFANG der Linkliste der gewählten Kategorie hinzu
    array_unshift($categories[$categoryId]['links'], $newLink);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Category ID does not exist.']);
    exit;
}

// 6. Das aktualisierte Array zurück in PHP-Code konvertieren
// Setzen des zweiten Arguments von var_export auf true gibt den Code als String zurück
$newPhpArrayString = var_export($categories, true);

// 7. Speichern der aktualisierten data.php
// Ersetzen des alten $categories-Blocks durch den neuen var_export-Block
$newContent = preg_replace(
    '/\$categories\s*=\s*.*?;/s', 
    "\$categories = " . $newPhpArrayString . ";", 
    $content,
    1 // Limitiere auf 1 Ersetzung
);

// FILE_LOCK | LOCK_EX ist wichtig, um Schreibkonflikte zu vermeiden
if (file_put_contents($dataFile, $newContent, LOCK_EX) !== false) {
    echo json_encode(['success' => true, 'message' => 'Link successfully added to data.php.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write to data.php. Check file permissions!']);
}
?>

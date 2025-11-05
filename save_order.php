<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$jsonFile = 'order.json';

// Validierung: Es muss mindestens eines der beiden Haupt-Arrays enthalten sein
if (
    !isset($data['category_order']) && 
    !isset($data['bubble_orders']) && 
    !isset($data['default_categories'])
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order data received. (Missing category or bubble order array.)']);
    exit;
}

// 1. Lade existierende Daten (falls vorhanden)
$existingData = [];
if (file_exists($jsonFile)) {
    $existingData = json_decode(file_get_contents($jsonFile), true);
    if (!is_array($existingData)) {
        $existingData = [];
    }
}

// 2. Neue Daten mit existierenden Daten mergen (überschreiben)
$newData = array_merge($existingData, $data);

// 3. Speichern
if (file_put_contents($jsonFile, json_encode($newData, JSON_PRETTY_PRINT)) !== false) {
    echo json_encode(['success' => true, 'message' => 'Order and bubble positions saved successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write to order.json. Check file permissions!']);
}
?>

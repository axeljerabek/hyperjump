<?php
// WICHTIG: Ersetzen Sie die folgenden Platzhalter durch Ihre tatsächlichen Werte
$api_key = 'HIER-DEN-OPENWEATHERMAP-API-KEY-REIN';
$city = 'Munich,DE'; // Oder Ihr Standort
$units = 'metric'; // 'metric' für Celsius, 'imperial' für Fahrenheit

$url = "http://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$api_key}&units={$units}";

// Cache-Mechanismus (optional, aber empfohlen, um API-Limits zu vermeiden)
$cache_file = 'weather_cache.json';
$cache_valid_time = 300; // 5 Minuten (in Sekunden)

if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_valid_time)) {
    // Lade aus Cache
    $response_data = json_decode(file_get_contents($cache_file), true);
} else {
    // API abfragen
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    
    $data = json_decode($response, true);

    if (isset($data['main']['temp'])) {
        // Erfolgreich, speichere Ergebnis für die Ausgabe
        $response_data = [
            'success' => true,
            'temp' => round($data['main']['temp']),
            'description' => $data['weather'][0]['description'],
            'icon' => $data['weather'][0]['icon']
        ];
        // Schreibe in den Cache
        file_put_contents($cache_file, json_encode($response_data));
    } else {
        // Fehler
        $response_data = ['success' => false, 'message' => 'API Error or Invalid Key.'];
    }
}

header('Content-Type: application/json');
echo json_encode($response_data);
?>

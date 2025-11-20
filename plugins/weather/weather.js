// Enthält die gesamte Logik für das Wetter-Widget

const WEATHER_API_URL = 'plugins/weather/weather.php';

/**
 * Ruft die Wetterdaten ab und aktualisiert die Anzeige.
 */
function updateWeather() {
    fetch(WEATHER_API_URL)
        .then(response => response.json())
        .then(data => {
            const display = document.getElementById('weatherDisplay');
            if (!display) return;

            if (data.success) {
                const weatherDescription = data.description.charAt(0).toUpperCase() + data.description.slice(1);

                display.innerHTML = `
                    <i class="fa-solid fa-cloud-sun weather-icon"></i>
                    ${data.temp}°C
                    <br>
                    ${weatherDescription}
                `;
            } else {
                display.textContent = `Error: ${data.message}`;
            }
        })
        .catch(error => console.error('Fehler beim Abrufen des Wetters:', error));
}

// Initialisierung und Intervalle für dieses Widget
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('weatherDisplay')) {
        updateWeather();
        setInterval(updateWeather, 600000); // Alle 10 Minuten
    }
});

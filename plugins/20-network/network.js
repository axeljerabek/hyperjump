// plugins/network/network.js
// Enthält die gesamte Logik für das Netzwerk-Traffic Widget

// WICHTIGE KORREKTUR: Der API-Pfad muss auf die Datei zeigen, die die JSON-Daten liefert
const NETWORK_API_URL = 'plugins/20-network/network_api.php'; 

/**
 * Aktualisiert die Live-Anzeige des Netzwerk-Traffics.
 */
function updateNetworkTraffic() {
    fetch(NETWORK_API_URL)
        .then(response => response.json())
        .then(data => {
            const widgetContainer = document.querySelector('.network-traffic-widget');
            
            // KORRIGIERTE SELEKTOREN: Wir suchen nach dem ersten und zweiten '.rate-value'
            // im Widget-Container.
            const rxEl = widgetContainer?.querySelector('.traffic-rate-item:nth-child(1) .rate-value');
            const txEl = widgetContainer?.querySelector('.traffic-rate-item:nth-child(2) .rate-value');

            if (rxEl && txEl) {
                rxEl.textContent = data.rx_rate_kbps.toFixed(2) + ' KB/s';
                txEl.textContent = data.tx_rate_kbps.toFixed(2) + ' KB/s';

                const rxRate = data.rx_rate_kbps;
                
                if (widgetContainer) {
                    let colorClass = 'traffic-green';
                    if (rxRate > 1000) colorClass = 'traffic-yellow';
                    if (rxRate > 5000) colorClass = 'traffic-orange';
                    if (rxRate > 10000) colorClass = 'traffic-red';

                    // Entferne alle alten Traffic-Klassen, um nur die neue zu setzen
                    // Der Regex entfernt alle Klassen, die mit 'traffic-' beginnen
                    // FÜGEN SIE AUCH DIE GRUNDKLASSE 'network-traffic-widget' WIEDER HINZU!
                    widgetContainer.className = 'network-traffic-widget ' + colorClass; 
                }
            }
        })
        .catch(error => {
            console.error('Fehler beim Abrufen des Netzwerk-Traffics:', error);
            const widgetContainer = document.querySelector('.network-traffic-widget');
            // Suche die Elemente auch im Fehlerfall korrekt
            const rxEl = widgetContainer?.querySelector('.traffic-rate-item:nth-child(1) .rate-value');
            const txEl = widgetContainer?.querySelector('.traffic-rate-item:nth-child(2) .rate-value');
            
            if (rxEl) rxEl.textContent = '--- KB/s';
            if (txEl) txEl.textContent = '--- KB/s';
        });
}

// Initialisierung und Intervalle für dieses Widget
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.network-traffic-widget')) {
        updateNetworkTraffic();
        // Aktualisierung alle 1 Sekunde
        setInterval(updateNetworkTraffic, 1000); 
    }
});

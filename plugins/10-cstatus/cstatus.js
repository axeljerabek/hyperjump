// Enthält die gesamte Logik für das Sentinel/Kamera-Status Widget

// KORREKTUR: Der Pfad wurde auf den neuen Ordner plugins/10-cstatus/ angepasst.
const CSTATUS_API_URL = 'plugins/10-cstatus/cstatus.php';

/**
 * Aktualisiert den Status des Kamerawidgets.
 */
function updateCameraStatus() {
    const indicatorsContainer = document.getElementById('cameraIndicators');

    if (!indicatorsContainer) return;

    fetch(CSTATUS_API_URL)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                indicatorsContainer.innerHTML = data.html;
                indicatorsContainer.closest('.camera-status-widget')?.classList.toggle('motion-alert', data.motion_detected);
            } else {
                indicatorsContainer.innerHTML = '<span class="status-error">' + data.html + '</span>';
                indicatorsContainer.closest('.camera-status-widget')?.classList.remove('motion-alert');
            }
        })
        .catch(error => {
            console.error('Fehler beim Laden des Kamera-Status:', error);
            indicatorsContainer.innerHTML = '<span class="status-error">Verbindungsfehler 💔</span>';
            indicatorsContainer.closest('.camera-status-widget')?.classList.remove('motion-alert');
        });
}

// Initialisierung und Intervalle für dieses Widget
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('cameraIndicators')) {
        updateCameraStatus();
        setInterval(updateCameraStatus, 3000);
    }
});

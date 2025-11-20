// plugins/nvidia/nvidia.js
// Enthält die gesamte Logik für das NVIDIA GPU Widget

const NVIDIA_API_URL = 'plugins/nvidia/nvidia_api.php'; 

/**
 * Aktualisiert die Live-Anzeige der NVIDIA GPU Auslastung.
 */
function updateNvidiaGpu() {
    fetch(NVIDIA_API_URL) 
        .then(response => response.json())
        .then(data => {
            const utilBar = document.getElementById('gpuUtilBar');
            const utilValue = document.getElementById('gpuUtilValue');
            const memoryValue = document.getElementById('gpuMemoryValue');
            
            // NEU: Speicherauslastungsbalken-Elemente abrufen
            const memBar = document.getElementById('gpuMemBar');
            const memBarValue = document.getElementById('gpuMemBarValue');
            
            const widget = document.querySelector('.nvidia-gpu-widget');
            
            if (!utilBar || !utilValue || !memoryValue || !widget || !memBar || !memBarValue) {
                console.warn('NVIDIA GPU Widget-Elemente nicht gefunden.');
                return;
            }

            // Fehlerprüfung
            if (data.error) {
                utilValue.textContent = '0.00%';
                memoryValue.textContent = '--- / ---';
                memBarValue.textContent = '--- %';
                utilBar.style.width = '0%';
                memBar.style.width = '0%';
                widget.classList.add('error-state'); 
                console.error('NVIDIA GPU API Error:', data.error);
                return;
            }

            const util = data.gpuUtilization;
            const used = data.memoryUsage.used;
            const total = data.memoryUsage.total;
            const memPercent = total > 0 ? (used / total) * 100 : 0; // Speicherauslastung in Prozent

            // 1. GPU Utilization (Load) anzeigen
            utilBar.style.width = util.toFixed(1) + '%';
            utilValue.textContent = util.toFixed(1) + '%';

            // 2. Memory Load Balken anzeigen
            memBar.style.width = memPercent.toFixed(1) + '%';
            memBarValue.textContent = memPercent.toFixed(1) + '%';

            // 3. Memory Usage (MiB / MiB) anzeigen
            memoryValue.textContent = `${used} MiB / ${total} MiB`;

            // Farbanpassung (Basierend auf CPU-Load, wie gewünscht)
            if (widget) {
                // Zuerst alle alten Farbstufen entfernen
                widget.classList.remove('error-state', 'gpu-low', 'gpu-medium', 'gpu-high');
                
                let colorClass = 'gpu-low'; // Standard: Grün
                
                if (util > 90) { 
                    colorClass = 'gpu-high'; // Rot bei über 90%
                } else if (util > 50) {
                    colorClass = 'gpu-medium'; // Gelb/Orange bei über 50%
                }
                
                // Hinzufügen der neuen Farbstufen-Klasse
                widget.classList.add(colorClass);
            }
        })
        .catch(error => {
            console.error('Fehler beim Abrufen der GPU-Daten:', error);
            const utilValue = document.getElementById('gpuUtilValue');
            const memoryValue = document.getElementById('gpuMemoryValue');
            const memBarValue = document.getElementById('gpuMemBarValue');
            
            if (utilValue) utilValue.textContent = '--- %';
            if (memoryValue) memoryValue.textContent = '--- MiB / --- MiB';
            if (memBarValue) memBarValue.textContent = '--- %';
        });
}

// Initialisierung und Intervalle für dieses Widget
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.nvidia-gpu-widget')) {
        updateNvidiaGpu();
        // Aktualisierung alle 3 Sekunden
        setInterval(updateNvidiaGpu, 3000); 
    }
});

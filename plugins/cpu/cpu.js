// plugins/cpu/cpu.js
// Enthält die gesamte Logik für das CPU Usage Widget

const CPU_API_URL = 'plugins/cpu/cpu_api.php'; 
// Hinweis: Bitte prüfen Sie, ob Ihre API-Datei cpu_api.php oder cpu_widget.php heißt.

/**
 * Wendet die entsprechende Farbstufen-Klasse an.
 * Nutzt die Farbschwellenwerte, die Sie für NVIDIA gewünscht haben (50% und 90%).
 */
function getCpuColorClass(utilization) {
    if (utilization > 90) {
        return 'cpu-high'; // Rot
    } else if (utilization > 50) {
        return 'cpu-medium'; // Gelb/Orange
    } else {
        return 'cpu-low'; // Grün
    }
}

/**
 * Aktualisiert die Live-Anzeige der CPU-Auslastung und der Core-Balken.
 */
function updateCpuUsage() {
    fetch(CPU_API_URL) 
        .then(response => response.json())
        .then(data => {
            const overallValueEl = document.getElementById('cpuOverallValue');
            const coreContainer = document.getElementById('cpuCoreContainer');

            if (!overallValueEl || !coreContainer) {
                console.warn('CPU-Widget-Container nicht gefunden.');
                return;
            }

            // Fehlerbehandlung oder leere Daten
            if (!data.cores || data.cores.length === 0) {
                 overallValueEl.textContent = '--- %';
                 coreContainer.innerHTML = '<span class="error-message">Error or no CPU data.</span>';
                 return;
            }
            
            // 1. Gesamtauslastung aktualisieren
            overallValueEl.textContent = data.overall.toFixed(1) + ' %';
            
            // Initialisierungs-Nachricht entfernen
            if (coreContainer.querySelector('.loading-message')) {
                coreContainer.innerHTML = '';
            }

            // 2. Cores erstellen/aktualisieren
            data.cores.forEach((util, index) => {
                const coreId = `cpuCore-${index}`;
                let coreEl = document.getElementById(coreId);

                // Element erstellen, wenn es nicht existiert
                if (!coreEl) {
                    coreEl = document.createElement('div');
                    coreEl.className = 'core-item';
                    coreEl.id = coreId;
                    
                    coreEl.innerHTML = `
                        <span class="core-label">Core ${index}</span>
                        <div class="progress-container">
                            <div class="progress-bar"></div>
                        </div>
                        <span class="core-value">0.0 %</span>
                    `;
                    coreContainer.appendChild(coreEl);
                }

                const progressBar = coreEl.querySelector('.progress-bar');
                const coreValue = coreEl.querySelector('.core-value');
                
                // Werte setzen
                progressBar.style.width = util.toFixed(1) + '%';
                coreValue.textContent = util.toFixed(1) + ' %';

                // Farbanpassung vornehmen
                const colorClass = getCpuColorClass(util);

                // Entferne alte Farbstufen-Klassen und setze die neue auf dem Balken
                progressBar.classList.remove('cpu-low', 'cpu-medium', 'cpu-high');
                progressBar.classList.add(colorClass);
            });
            
            // Optional: Alte Kerne entfernen, falls die Anzahl der Kerne abnimmt (selten)
            while (coreContainer.children.length > data.cores.length) {
                coreContainer.removeChild(coreContainer.lastChild);
            }

        })
        .catch(error => {
            console.error('Fehler beim Abrufen der CPU-Daten:', error);
            const overallValueEl = document.getElementById('cpuOverallValue');
            if (overallValueEl) overallValueEl.textContent = '--- %';
        });
}

// Initialisierung und Intervalle
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.cpu-usage-widget')) {
        updateCpuUsage();
        // Aktualisierung alle 1-3 Sekunden
        setInterval(updateCpuUsage, 2000); 
    }
});

// plugins/ram/ram.js
// Enthält die gesamte Logik für das RAM Usage Widget

const RAM_API_URL = 'plugins/ram/ram_api.php'; 

/**
 * Konvertiert Byte-Zahlen in lesbare MiB/GiB-Strings.
 */
function formatBytes(bytes) {
    if (bytes === 0) {
        return '0 MiB';
    }
    const KB = 1024;
    const MiB = KB * 1024;
    const GiB = MiB * 1024;
    
    if (bytes >= GiB) {
        return (bytes / GiB).toFixed(2) + ' GiB';
    }
    return (bytes / MiB).toFixed(2) + ' MiB';
}

/**
 * Wendet die entsprechende Farbstufen-Klasse an (Grün bis Rot).
 */
function getRamColorClass(percent) {
    if (percent > 90) {
        return 'ram-high'; // Rot
    } else if (percent > 70) {
        return 'ram-medium'; // Gelb/Orange
    } else {
        return 'ram-low'; // Grün
    }
}

/**
 * Aktualisiert die Live-Anzeige der RAM-Auslastung.
 */
function updateRamUsage() {
    fetch(RAM_API_URL) 
        .then(response => response.json())
        .then(data => {
            const widget = document.querySelector('.ram-usage-widget');
            
            if (data.error || !data.mem || !data.swap) {
                if (widget) widget.classList.add('error-state');
                console.error('RAM API Error:', data.error || 'Invalid data structure');
                return;
            }

            const mem = data.mem;
            const swap = data.swap;

            // --- 1. RAM Hauptbalken (Used/Total) ---
            const utilBar = document.getElementById('ramUtilBar');
            const utilValue = document.getElementById('ramUtilValue');
            const usagePercent = mem.usage_percent;

            if (utilBar && utilValue) {
                utilBar.style.width = usagePercent + '%';
                utilValue.textContent = usagePercent.toFixed(1) + ' %';

                // Farbanpassung auf dem Widget-Container
                const colorClass = getRamColorClass(usagePercent);
                if (widget) {
                    widget.classList.remove('error-state', 'ram-low', 'ram-medium', 'ram-high');
                    widget.classList.add(colorClass);
                }
            }

            // --- 2. RAM Details ---
            document.getElementById('ramTotalValue').textContent = formatBytes(mem.total);
            document.getElementById('ramFreeValue').textContent = formatBytes(mem.free);
            document.getElementById('ramAvailableValue').textContent = formatBytes(mem.available);
            document.getElementById('ramCachedValue').textContent = formatBytes(mem.cached);

            // --- 3. SWAP Balken und Details ---
            const swapBar = document.getElementById('swapUtilBar');
            const swapValue = document.getElementById('swapUtilValue');
            const swapUsedTotalValue = document.getElementById('swapUsedTotalValue');
            const swapPercent = swap.usage_percent;

            if (swapBar && swapValue && swapUsedTotalValue) {
                swapBar.style.width = swapPercent + '%';
                swapValue.textContent = swapPercent.toFixed(1) + ' %';
                
                swapUsedTotalValue.textContent = `${formatBytes(swap.used)} / ${formatBytes(swap.total)}`;

                // Swap-Balken-Farbe setzen (Swap ist oft Gelb/Orange, wenn genutzt)
                const swapColorClass = (swapPercent > 0) ? 'swap-used' : 'swap-free';
                swapBar.classList.remove('swap-used', 'swap-free');
                swapBar.classList.add(swapColorClass);
            }
        })
        .catch(error => {
            console.error('Fehler beim Abrufen der RAM-Daten:', error);
            const widget = document.querySelector('.ram-usage-widget');
            if (widget) widget.classList.add('error-state');
        });
}

// Initialisierung und Intervalle
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.ram-usage-widget')) {
        updateRamUsage();
        // Aktualisierung alle 5 Sekunden
        setInterval(updateRamUsage, 5000); 
    }
});

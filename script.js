// Konfigurationskonstanten
const LIGHT_MODE_HOUR = 7;
const DARK_MODE_HOUR = 20;
const COLLAPSED_CATEGORIES_COOKIE = 'collapsedCats'; // Neuer Cookie-Name

// --- Hilfsfunktionen für Cookies ---
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
    let nameEQ = name + "=";
    let ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// --- Dark Mode Logik (Unverändert) ---
function applyMode(mode) {
    let finalMode = mode;

    if (mode === 'auto') {
        const now = new Date();
        const hour = now.getHours();
        if (hour >= LIGHT_MODE_HOUR && hour < DARK_MODE_HOUR) {
            finalMode = 'light';
        } else {
            finalMode = 'dark';
        }
    }

    document.body.classList.remove('light-mode', 'dark-mode');

    if (finalMode === 'light') {
        document.body.classList.add('light-mode');
    } else if (finalMode === 'dark') {
        document.body.classList.add('dark-mode');
    }

    const switchEl = document.getElementById('modeSwitch');
    if (switchEl) {
        switchEl.setAttribute('data-mode', mode);
        let icon = '';
        if (mode === 'auto') {
            icon = `<i class="fa-solid fa-sync-alt"></i> ${finalMode === 'light' ? 'Day' : 'Night'} (Auto)`;
        } else if (mode === 'light') {
            icon = '<i class="fa-solid fa-sun"></i> Day Mode';
        } else {
            icon = '<i class="fa-solid fa-moon"></i> Night Mode';
        }
        switchEl.innerHTML = icon;
    }

    setCookie('colorMode', mode, 365);
}

function toggleMode() {
    const currentMode = getCookie('colorMode') || 'auto';
    let newMode = 'auto';

    if (currentMode === 'auto') {
        newMode = 'light';
    } else if (currentMode === 'light') {
        newMode = 'dark';
    } else {
        newMode = 'auto';
    }

    applyMode(newMode);
}

// --- Größen-Toggle Logik (Unverändert) ---
function applySizeMode(mode) {
    document.body.classList.remove('compact-mode', 'normal-mode');
    if (mode === 'compact') {
        document.body.classList.add('compact-mode');
    } else {
        document.body.classList.add('normal-mode');
    }
    setCookie('sizeMode', mode, 365);
    updateSizeSwitchUI(mode);
}

function toggleSizeMode() {
    const currentMode = getCookie('sizeMode') || 'compact';
    const newMode = (currentMode === 'compact') ? 'normal' : 'compact';
    applySizeMode(newMode);
}

function updateSizeSwitchUI(mode) {
    const switchEl = document.getElementById('sizeSwitch');
    if (switchEl) {
        switchEl.setAttribute('data-mode', mode);
        const text = mode === 'compact' ? 'Compact View' : 'Normal View';
        const icon = mode === 'compact' ? 'fa-compress-alt' : 'fa-expand-alt';
        switchEl.innerHTML = `<i class="fa-solid ${icon}"></i> ${text}`;
    }
}

// --- Intelligente Uhrzeit/Datum (Unverändert) ---
function updateDateTime() {
    const now = new Date();
    const dateStr = now.toLocaleDateString('de-DE', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    const timeStr = now.toLocaleTimeString('de-DE', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    const dateTimeEl = document.getElementById('currentDateTime');
    if (dateTimeEl) {
        dateTimeEl.innerHTML = `${dateStr}<br>${timeStr}`;
    }
}

// --- Wetter-Funktion (Unverändert) ---
function updateWeather() {
    fetch('get_weather.php')
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

// --- Systemlast-Funktionen (Unverändert) ---
function getColorClass(value) {
    const thresholds = [25, 50, 75, 90];
    const colorClasses = ["green", "light-green", "light-orange", "orange", "red"];

    for (let i = 0; i < thresholds.length; i++) {
        if (value <= thresholds[i]) {
            return colorClasses[i];
        }
    }
    return colorClasses[colorClasses.length - 1];
}

function updateProgressBar(barId, value) {
    const progressBar = document.getElementById(barId);
    if (!progressBar) return;
    const bar = progressBar.querySelector(".bar");
    const barText = progressBar.querySelector(".bar-text");

    // Farbe und Fortschritt setzen
    progressBar.className = "progress-bar " + getColorClass(value);
    bar.style.width = (value) + "%";
    barText.textContent = (value).toFixed(2) + "%";
}

function updateCoreBars(coreLoads) {
    const coreContainer = document.getElementById('coreContainer');
    if (!coreContainer) return;
    coreContainer.innerHTML = "";

    coreLoads.forEach((coreLoad, index) => {
        const coreBar = document.createElement("div");
        coreBar.className = "core-bar " + getColorClass(coreLoad);

        const loadWidth = (coreLoad) + "%";
        coreBar.title = `Core ${index + 1}`;
        coreBar.innerHTML = `<div class="core-load" style="width: ${loadWidth}"></div>`;

        const coreText = document.createElement("div");
        coreText.className = "core-bar-text";
        coreText.textContent = (coreLoad).toFixed(1) + "%";

        coreBar.appendChild(coreText);
        coreContainer.appendChild(coreBar);
    });
}

function updateGPUBar(data) {
    const gpuProgressBar = document.getElementById('gpuProgressBar');
    if (!gpuProgressBar) return;

    const gpuBar = gpuProgressBar.querySelector('.bar');
    const gpuBarText = gpuProgressBar.querySelector('.bar-text');
    const gpuMemoryText = document.getElementById('gpuMemoryText');

    const gpuUtilization = data.gpuUtilization;

    const usedMemory = data.memoryUsage.used;
    const totalMemory = data.memoryUsage.total;
    // NEUE LOGIK FÜR VRAM-AUSLASTUNG ALS PROZENT
    const vramUsagePercent = (totalMemory > 0) ? (usedMemory / totalMemory) * 100 : 0;

    // GPU-Auslastung (Utilizaton) Bar aktualisieren
    gpuProgressBar.className = 'progress-bar ' + getColorClass(gpuUtilization);
    gpuBar.style.width = gpuUtilization + '%';
    gpuBarText.textContent = gpuUtilization.toFixed(1) + '%';

    // VRAM-Auslastung (Speicher) Text aktualisieren und Prozentsatz anzeigen
    const vramColorClass = getColorClass(vramUsagePercent);
    gpuMemoryText.innerHTML = `${usedMemory} MB / ${totalMemory} MB <strong>(${vramUsagePercent.toFixed(1)}%)</strong>`;

    const gpuMemoryWrap = gpuMemoryText.closest('.gpu-wrap');
    if (gpuMemoryWrap) {
        gpuMemoryWrap.className = 'progress-bar-wrap gpu-wrap vram-' + vramColorClass;
    }
}

function updateCoreLoads() {
    fetch('update-core-load.php')
        .then(response => response.json())
        .then(data => updateCoreBars(data.coreLoads))
        .catch(error => console.error('Fehler beim Abrufen der Kernauslastung:', error));
}

function updateSystemLoad() {
    fetch('update.php')
        .then(response => response.json())
        .then(data => {
            updateProgressBar("cpuProgressBar", data.cpuLoad);
            updateProgressBar("ramProgressBar", data.ramUsage);
            updateProgressBar("swapProgressBar", data.swapUsage);
        })
        .catch(error => console.error('Fehler beim Abrufen der Daten:', error));
}

function updateGPUData() {
    fetch('update-nv.php')
        .then(response => response.json())
        .then(data => updateGPUBar(data))
        .catch(error => console.error('Fehler beim Abrufen der GPU-Daten:', error));
}

// --- Sortierungs-Logik (Unverändert) ---
function getCurrentBubbleOrder(container) {
    return Array.from(container.children)
        .map(el => el.querySelector('.bubble-text').textContent.trim());
}

function getCurrentCategoryOrder() {
    const grid = document.querySelector('.categories-grid');
    if (!grid) return [];
    return Array.from(grid.children)
        .filter(el => el.classList.contains('category-group'))
        .map(el => el.getAttribute('data-id'));
}

function saveOrder(orderArray, type = 'category_order') {
    const dataToSave = {};

    if (type === 'category_order') {
        dataToSave['category_order'] = orderArray;

        const bubbleOrders = {};
        document.querySelectorAll('.category-group').forEach(group => {
            const categoryId = group.getAttribute('data-id');
            const linksContainer = group.querySelector('.links-container');
            bubbleOrders[categoryId] = getCurrentBubbleOrder(linksContainer);
        });
        dataToSave['bubble_orders'] = bubbleOrders;

    } else {
        dataToSave[type] = orderArray;
    }

    fetch('save_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dataToSave)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(data.message);
            } else {
                console.error('Save failed:', data.message);
            }
        })
        .catch(error => console.error('Error saving order:', error));
}

function resetOrder() {
    if (!confirm('Sind Sie sicher, dass Sie die Reihenfolge auf den Originalzustand zurücksetzen möchten?')) {
        return;
    }

    const defaultOrderElement = document.getElementById('defaultOrder');
    if (!defaultOrderElement) {
        alert('Fehler: Die Standardreihenfolge konnte nicht gefunden werden.');
        return;
    }
    const defaultOrder = JSON.parse(defaultOrderElement.textContent);

    const resetData = {
        category_order: defaultOrder,
        bubble_orders: {}
    };

    fetch('save_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(resetData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Fehler beim Zurücksetzen: ' + data.message);
            }
        })
        .catch(error => console.error('Error saving order:', error));
}

function initSortableGrid() {
    const grid = document.querySelector('.categories-grid');
    if (!grid || typeof Sortable === 'undefined') return;

    // Sortierung für Kategorien
    new Sortable(grid, {
        animation: 250,
        delay: 50,
        filter: ".system-panel",
        fallbackOnBody: true,
        swapThreshold: 0.65,
        handle: ".category-title",
        onEnd: function (evt) {
            const newOrder = getCurrentCategoryOrder();
            saveOrder(newOrder, 'category_order');
        },
    });

    // Sortierung für Bubbles innerhalb jeder Kategorie
    document.querySelectorAll('.links-container').forEach(container => {
        new Sortable(container, {
            animation: 150,
            group: 'bubbles',
            // WICHTIGE ÄNDERUNG: Setzt das Icon als einziges Handle für Drag & Drop
            handle: '.bubble-icon', 
            // Optional: Verzögert das Ziehen leicht, um normale Klicks zu bevorzugen
            delay: 100, 
            onEnd: function (evt) {
                const currentCategoryOrder = getCurrentCategoryOrder();
                saveOrder(currentCategoryOrder, 'category_order');
            }
        });
    });
}

// --- Quick Link Form Logik (Unverändert) ---
function initAddLink() {
    const form = document.getElementById('addLinkForm');
    if (!form) return;

    const messageEl = document.getElementById('formMessage');
    const submitButton = document.getElementById('submitLinkButton');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        messageEl.textContent = 'Saving...';
        messageEl.classList.remove('success', 'error');
        submitButton.disabled = true;

        const linkData = {
            text: document.getElementById('linkText').value,
            url: document.getElementById('linkUrl').value,
            icon: document.getElementById('linkIcon').value || 'link',
            category: document.getElementById('linkCategory').value
        };

        fetch('add_link.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(linkData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageEl.textContent = 'Link erfolgreich hinzugefügt! Seite wird neu geladen...';
                    messageEl.classList.add('success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    messageEl.textContent = 'FEHLER: ' + (data.message || 'Unbekannter Fehler.');
                    messageEl.classList.add('error');
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Network Error:', error);
                messageEl.textContent = 'FEHLER: Netzwerkproblem beim Speichern.';
                messageEl.classList.add('error');
                submitButton.disabled = false;
            });
    });
}

// --- NEUE Collapsible-Logik ---

/**
 * Speichert den Status der eingeklappten Kategorien in einem Cookie.
 */
function saveCollapsedState(collapsedCategories) {
    setCookie(COLLAPSED_CATEGORIES_COOKIE, collapsedCategories.join(','), 365);
}

/**
 * Liest den gespeicherten Zustand der Kategorien aus dem Cookie.
 * @returns {Array<string>} Array von Category-IDs, die eingeklappt sein sollen.
 */
function getCollapsedState() {
    const cookie = getCookie(COLLAPSED_CATEGORIES_COOKIE);
    return cookie ? cookie.split(',') : [];
}

/**
 * Schaltet den Collapsible-Zustand einer Kategorie um.
 * @param {HTMLElement} categoryGroup Das .category-group Element.
 */
function toggleCategoryCollapse(categoryGroup) {
    const categoryId = categoryGroup.getAttribute('data-category-id');
    const isCollapsed = categoryGroup.classList.toggle('collapsed');

    // ARIA-Attribut aktualisieren (Zugänglichkeit)
    categoryGroup.setAttribute('aria-expanded', !isCollapsed);

    // Cookie-Status aktualisieren
    const collapsedCategories = getCollapsedState().filter(id => id !== categoryId); // ID entfernen
    if (isCollapsed) {
        collapsedCategories.push(categoryId); // ID hinzufügen, wenn eingeklappt
    }

    saveCollapsedState(collapsedCategories);
}

/**
 * Initialisiert die Collapsible-Funktion und stellt den gespeicherten Zustand wieder her.
 */
function initCategoryCollapse() {
    const collapsedCategories = getCollapsedState();

    document.querySelectorAll('.category-group').forEach(group => {
        const categoryId = group.getAttribute('data-category-id');
        const titleElement = group.querySelector('.category-title');

        // 1. Gespeicherten Zustand wiederherstellen
        if (collapsedCategories.includes(categoryId)) {
            group.classList.add('collapsed');
            group.setAttribute('aria-expanded', 'false');
        } else {
             group.setAttribute('aria-expanded', 'true');
        }

        // 2. Event Listener hinzufügen (zum Umschalten)
        // Klick auf den gesamten Titel, außer auf das Drag-Handle
        titleElement.addEventListener('click', (e) => {
            // Prüfen, ob das geklickte Element oder ein Elternelement das Drag-Handle ist
            if (e.target.closest('.drag-handle')) {
                return; // Nicht umschalten, wenn das Drag-Handle geklickt wird (SortableJS)
            }
            toggleCategoryCollapse(group);
        });

    });
}

// Starten der Anwendung beim Laden der Seite
document.addEventListener('DOMContentLoaded', () => {
    // Modus-Initialisierung
    const initialMode = getCookie('colorMode') || 'auto';
    applyMode(initialMode);

    // Größen-Modus Initialisierung
    const initialSizeMode = getCookie('sizeMode') || 'compact';
    applySizeMode(initialSizeMode);

    // NEUE: Collapsible-Initialisierung
    initCategoryCollapse();

    // Initial-Updates
    updateDateTime();
    updateWeather();
    updateCoreLoads();
    updateSystemLoad();
    updateGPUData();
    initSortableGrid();
    initAddLink();

    // Event-Listener
    const resetOrderButton = document.getElementById('resetOrderButton');
    if(resetOrderButton) resetOrderButton.addEventListener('click', resetOrder);

    const modeSwitch = document.getElementById('modeSwitch');
    if(modeSwitch) modeSwitch.addEventListener('click', toggleMode);

    const sizeSwitch = document.getElementById('sizeSwitch');
    if(sizeSwitch) sizeSwitch.addEventListener('click', toggleSizeMode);

    // Intervalle
    setInterval(updateDateTime, 1000);
    setInterval(updateCoreLoads, 500);
    setInterval(updateSystemLoad, 1000);
    setInterval(updateGPUData, 500);
    setInterval(updateWeather, 600000);
    // Auto-Mode-Check alle 5 Minuten
    setInterval(() => {
        const currentMode = getCookie('colorMode');
        if (currentMode === 'auto') {
            applyMode('auto');
        }
    }, 5 * 60 * 1000);

    // Matomo Tracking Code
    var _paq = window._paq = window._paq || [];
    _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function() {
      var u="//analytics.jerabek.fi/";
      _paq.push(['setTrackerUrl', u+'matomo.php']);
      _paq.push(['setSiteId', '4']);
      var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
      g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
    })();
});

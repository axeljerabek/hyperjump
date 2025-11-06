<?php 
// 1. Session und Passwort-Definitionen laden (NEU: config.php muss existieren!)
include 'config.php'; 

// 2. Daten laden
include 'data.php'; 

// --- PHP-Logik zum Laden und Sortieren der Kategorien und Bubbles ---
$jsonFile = 'order.json';
$categoryOrder = [];
$bubbleOrders = []; 

// Versuche, die gespeicherte Reihenfolge zu laden
if (file_exists($jsonFile)) {
    $orderData = json_decode(file_get_contents($jsonFile), true);
    
    if (isset($orderData['category_order']) && is_array($orderData['category_order'])) {
        $categoryOrder = $orderData['category_order'];
    } elseif (isset($orderData['default_categories']) && is_array($orderData['default_categories'])) {
        $categoryOrder = $orderData['default_categories'];
    }
    
    // Lade die Bubble-Reihenfolge
    $bubbleOrders = isset($orderData['bubble_orders']) ? $orderData['bubble_orders'] : [];
    
} else {
    $categoryOrder = array_keys($categories);
}

// 3. Sortiere die Kategorien basierend auf $categoryOrder
$sortedCategories = [];
foreach ($categoryOrder as $key) {
    if (isset($categories[$key])) {
        
        // Sortiere die Bubbles in der Kategorie, falls eine gespeicherte Reihenfolge existiert
        if (isset($bubbleOrders[$key]) && is_array($bubbleOrders[$key])) {
            $sortedLinks = [];
            $currentLinks = $categories[$key]['links'];
            
            foreach ($bubbleOrders[$key] as $linkText) {
                // Finde den Link anhand seines Textes (Text als einfacher Schlüssel)
                $found = false;
                foreach ($currentLinks as $link) {
                    if (isset($link['text']) && $link['text'] === $linkText) {
                        $sortedLinks[] = $link;
                        $found = true;
                        break;
                    }
                }
            }
            // Füge neue Links, die noch nicht in der order.json stehen, ans Ende hinzu
            $existingTexts = array_map(fn($l) => isset($l['text']) ? $l['text'] : '', $sortedLinks);
            foreach ($currentLinks as $link) {
                if (isset($link['text']) && !in_array($link['text'], $existingTexts)) {
                    $sortedLinks[] = $link;
                }
            }
            $categories[$key]['links'] = $sortedLinks;
        }
        
        $sortedCategories[$key] = $categories[$key];
    }
}
// Füge fehlende neue Kategorien am Ende hinzu
foreach ($categories as $key => $category) {
    if (!isset($sortedCategories[$key])) {
        $sortedCategories[$key] = $category;
    }
}
$categories = $sortedCategories; // Verwende die sortierte Liste
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Axel Jerabek hyperjump site</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Fira+Code:400,700|Source+Sans+Pro:400,700">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <link rel="stylesheet" href="styles2.css">

    <script>
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
            for(let i=0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        }

        // --- Dark Mode Logik ---
        const LIGHT_MODE_HOUR = 7; 
        const DARK_MODE_HOUR = 20; 

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
            if(switchEl) {
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
        
        // --- Größen-Toggle Logik ---
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
            if(switchEl) {
                switchEl.setAttribute('data-mode', mode);
                const text = mode === 'compact' ? 'Compact View' : 'Normal View';
                const icon = mode === 'compact' ? 'fa-compress-alt' : 'fa-expand-alt';
                switchEl.innerHTML = `<i class="fa-solid ${icon}"></i> ${text}`;
            }
        }

        // --- Intelligente Uhrzeit/Datum ---
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
        
        // --- Wetter-Funktion (benötigt get_weather.php) ---
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

        // --- Systemlast-Funktionen (Platzhalter, müssen in update.php etc. implementiert sein!) ---
        function updateProgressBar(barId, value) { 
            const progressBar = document.getElementById(barId);
            if (!progressBar) return;
            const bar = progressBar.querySelector(".bar");
            const barText = progressBar.querySelector(".bar-text");
            const colorClasses = ["green", "light-green", "light-orange", "orange", "red"];
            const loadValue = value / 100; 
            const colorIndex = Math.min(Math.floor(loadValue * colorClasses.length), colorClasses.length - 1);

            progressBar.className = "progress-bar " + getColorClass(value);
            bar.style.width = (value) + "%";
            barText.textContent = (value).toFixed(2) + "%";
        }

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

        // *** KORRIGIERTE FUNKTION ZUR KORREKTEN ANZEIGE DES VRAM-PROZENTSATZES ***
        function updateGPUBar(data) {
            const gpuProgressBar = document.getElementById('gpuProgressBar');
            if (!gpuProgressBar) return;

            const gpuBar = gpuProgressBar.querySelector('.bar');
            const gpuBarText = gpuProgressBar.querySelector('.bar-text');
            const gpuMemoryText = document.getElementById('gpuMemoryText'); // Verwenden Sie die ID für das Element

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
            const vramColorClass = getColorClass(vramUsagePercent); // Farbe basierend auf VRAM-Auslastung
            
            // Optional: Füge Klasse für Textfarbe hinzu (erfordert entsprechende CSS)
            // Beachte: Der span-Container der den Text enthält, ist im HTML nicht direkt für Klassen vorgesehen
            // Wir aktualisieren den Textinhalt so, dass er den Prozentsatz enthält.
            gpuMemoryText.innerHTML = `${usedMemory} MB / ${totalMemory} MB <strong>(${vramUsagePercent.toFixed(1)}%)</strong>`;
            
            // Für die VRAM-Farbhervorhebung wird das Elternelement benötigt, oder direktes CSS-Styling
            const gpuMemoryWrap = gpuMemoryText.closest('.gpu-wrap');
            if (gpuMemoryWrap) {
                 // Füge eine Klasse zum Umhüllen-Element hinzu, um die Textfarbe basierend auf VRAM-Auslastung zu steuern
                 gpuMemoryWrap.className = 'progress-bar-wrap gpu-wrap vram-' + vramColorClass; 
            }
        }
        // *** ENDE KORRIGIERTE FUNKTION ***

        function updateCoreLoads() {
            // Dies erfordert die Implementierung von update-core-load.php
            fetch('update-core-load.php')
                .then(response => response.json())
                .then(data => updateCoreBars(data.coreLoads))
                .catch(error => console.error('Fehler beim Abrufen der Kernauslastung:', error));
        }

        function updateSystemLoad() {
             // Dies erfordert die Implementierung von update.php
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
            // Dies erfordert die Implementierung von update-nv.php
            fetch('update-nv.php')
                .then(response => response.json())
                .then(data => updateGPUBar(data))
                .catch(error => console.error('Fehler beim Abrufen der GPU-Daten:', error));
        }
        // --- Ende Systemlast-Funktionen ---

        // --- Sortierungs-Logik ---
        
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

            // NEU: Session-Prüfung für save_order.php ist erforderlich! (Dies wird im Backend behandelt)
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
            if (!grid) return;

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

            document.querySelectorAll('.links-container').forEach(container => {
                new Sortable(container, {
                    animation: 150,
                    group: 'bubbles', 
                    onEnd: function (evt) {
                        const currentCategoryOrder = getCurrentCategoryOrder(); 
                        saveOrder(currentCategoryOrder, 'category_order'); 
                    }
                });
            });
        }
        
        // --- Quick Link Form Logik ---
        function initAddLink() {
            const form = document.getElementById('addLinkForm');
            // Wenn das Formular nicht existiert (weil nicht eingeloggt), breche ab
            if (!form) return; 

            const messageEl = document.getElementById('formMessage');
            const submitButton = document.getElementById('submitLinkButton');

            form.addEventListener('submit', function(e) {
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


        // Starten der Anwendung beim Laden der Seite
        document.addEventListener('DOMContentLoaded', () => {
            // Modus-Initialisierung
            const initialMode = getCookie('colorMode') || 'auto';
            applyMode(initialMode);
            
            // Größen-Modus Initialisierung
            const initialSizeMode = getCookie('sizeMode') || 'compact'; 
            applySizeMode(initialSizeMode); 
            
            // System-Updates und Sortierung
            updateDateTime(); 
            updateWeather();
            updateCoreLoads();
            updateSystemLoad();
            updateGPUData();
            initSortableGrid(); 
            initAddLink(); // <<< Quick-Link Formular initialisieren (läuft nur wenn Formular im DOM ist)
            
            document.getElementById('resetOrderButton').addEventListener('click', resetOrder);
            document.getElementById('modeSwitch').addEventListener('click', toggleMode);
            document.getElementById('sizeSwitch').addEventListener('click', toggleSizeMode); 
        });

        // Intervalle
        setInterval(updateDateTime, 1000); 
        setInterval(updateCoreLoads, 500);
        setInterval(updateSystemLoad, 1000);
        setInterval(updateGPUData, 500);
        setInterval(updateWeather, 600000); 
        setInterval(() => {
            const currentMode = getCookie('colorMode');
            if (currentMode === 'auto') {
                applyMode('auto');
            }
        }, 5 * 60 * 1000); 

    </script>
    
    <script>
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
    </script>
    <noscript><p><img src="//analytics.jerabek.fi/matomo.php?idsite=4&rec=1" style="border:0;" alt="" /></p></noscript>
</head>

<body>
    <script id="defaultOrder" type="application/json">
        <?php echo json_encode(array_keys($categories)); ?>
    </script>
    
    <div id="video-background">
        <video autoplay muted loop>
            <source src="bgvideo.mp4" type="video/mp4">
        </video>
    </div>

    <header>
        <h1 class="header-title"><a href="https://axel.jerabek.fi">hyperjump to</a></h1>
        
        <div class="switch-group">
            <button id="modeSwitch" class="switch-button" data-mode="auto">
                <i class="fa-solid fa-sync-alt"></i> Auto Mode
            </button>
            <button id="sizeSwitch" class="switch-button" data-mode="compact">
                <i class="fa-solid fa-compress-alt"></i> Compact View
            </button>
        </div>
    </header>

    <div class="content-wrapper">
        
        <div class="categories-grid">
            <?php foreach ($categories as $categoryId => $category): ?>
                <div class="category-group" style="--category-color: <?= $category['color']; ?>" data-id="<?= $categoryId; ?>">
                    <h2 class="category-title"><i class="fa-solid fa-arrows-up-down-left-right drag-handle"></i> <?= $category['title']; ?></h2>
                    <div class="links-container">
                        <?php foreach ($category['links'] as $link): ?>
                            <a 
                                href="<?= $link['url']; ?>"
                                target="_blank" 
                                class="bubble-link <?= $link['disabled'] ? 'disabled' : ''; ?>"
                                title="<?= $link['disabled'] ? '(Unfunctional/Decommissioned)' : ''; ?>"

                                data-color="<?= htmlspecialchars($link['color'] ?? ''); ?>" 

                                style="<?= !empty($link['color']) ? '--accent-color: ' . htmlspecialchars($link['color']) . ';' : ''; ?>"
                            >

                                <i class="fa-solid fa-<?= $link['icon']; ?> bubble-icon"></i>
                                <span class="bubble-text"><?= $link['text']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="system-panel">
            
            <div class="datetime-widget">
                <h3 class="panel-title"><i class="fa-solid fa-clock"></i> Current Time</h3>
                <div id="currentDateTime" class="time-display">--</div>
            </div>
            
            <div class="weather-widget">
                <h3 class="panel-title"><i class="fa-solid fa-cloud-sun"></i> Weather (via API)</h3>
                <div class="weather-display" id="weatherDisplay">
                    Loading weather data...
                </div>
            </div>
            
            <h3 class="panel-title">System Utilization</h3>
            
            <div class="progress-container">
                <div class="progress-bar-wrap">
                    <div class="progress-bar" id="cpuProgressBar"><div class="bar"></div><div class="bar-text">CPU</div></div>
                    <div class="progress-label">System usage</div>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar" id="ramProgressBar"><div class="bar"></div><div class="bar-text">RAM</div></div>
                    <div class="progress-label">RAM usage</div>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar" id="swapProgressBar"><div class="bar"></div><div class="bar-text">SWAP</div></div>
                    <div class="progress-label">SWAP usage</div>
                </div>
            </div>

            <h3 class="panel-title core-title"><i class="fa-solid fa-microchip"></i> Processor Cores</h3>
            <div class="core-container" id="coreContainer"></div>
            
            <h3 class="panel-title gpu-title"><i class="fa-solid fa-gripfire"></i> Nvidia GPU Usage</h3>
            <div class="progress-bar-wrap gpu-wrap">
                <div class="progress-bar" id="gpuProgressBar"><div class="bar"></div><div class="bar-text">GPU</div></div>
                <div class="gpu-memory-text">VRAM: <span id="gpuMemoryText">0 MB / 0 MB</span></div>
            </div>
            
            <?php if (IS_LOGGED_IN): ?>
            
                <div class="quick-link-form">
                    <h3 class="panel-title"><i class="fa-solid fa-link"></i> Add New Link</h3>
                    <form id="addLinkForm">
                        <input type="text" id="linkText" placeholder="Name des Links (z.B. New Server)" required>
                        <input type="url" id="linkUrl" placeholder="URL (z.B. https://192.168.1.10)" required>
                        <input type="text" id="linkIcon" placeholder="Icon (z.B. server - siehe fontawesome.com)">
                        
                        <select id="linkCategory" required>
                            <option value="">-- Wähle Kategorie --</option>
                            
                            <?php 
                            // PHP-Loop, um die Kategorien dynamisch in die Dropdown-Liste zu laden
                            foreach ($categories as $categoryId => $category): ?>
                                <option value="<?= $categoryId; ?>"><?= $category['title']; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" id="submitLinkButton">Add Link</button>
                        <p id="formMessage" class="message"></p>
                    </form>
                    
                    <a href="admin.php" class="admin-link"><i class="fa-solid fa-user-shield"></i> Admin Panel</a>
                    <a href="login.php?logout=true" class="logout-link"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
                </div>
                
            <?php else: ?>
            
                <div class="quick-link-form login-form">
                    <h3 class="panel-title"><i class="fa-solid fa-lock"></i> Admin Login</h3>
                    <form action="login.php" method="POST" id="loginForm">
                        <input type="password" name="password" placeholder="Admin Passwort" required>
                        <button type="submit">Login</button>
                        <?php if (isset($_GET['login_error'])): ?>
                            <p class="message error" style="visibility: visible; height: auto;">Falsches Passwort!</p>
                        <?php endif; ?>
                    </form>
                </div>
            
            <?php endif; ?>
            <button id="resetOrderButton" class="reset-button">
                <i class="fa-solid fa-undo"></i> Reset Order
            </button>
            
            <a href="https://www.wetransco.de/" target="_blank" class="code-link">
                <i class="fa-solid fa-code"></i> find the code for this page on WeTransCo.de
            </a>
        </div>
        </div>
</body>
</html>

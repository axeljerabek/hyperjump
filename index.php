<?php
// 1. Session und Passwort-Definitionen laden (NEU: config.php muss existieren!)
include 'config.php';

// 2. Daten laden
include 'data.php';

// --- HILFSFUNKTION FÜR PLUGIN-NAMEN (OHNE UNSICHTBARE ZEICHEN) ---
// Diese Funktion entfernt numerische Präfixe (z.B. '10-' oder '99-') vom Ordnernamen, 
// sodass wir den ursprünglichen Dateinamen (z.B. 'cpu') erhalten, während die Sortierung erhalten bleibt.
function get_clean_plugin_name($dir_name) {
    if (preg_match('/^\d{1,2}-(.+)$/', $dir_name, $matches)) {
        return $matches[1]; // Gibt z.B. nur 'cpu' zurück
    }
    return $dir_name; // Gibt den vollen Namen zurück, falls kein Präfix vorhanden ist
}

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
    
    <?php
        // 🧭 AUTO-DISCOVERY DER PLUGIN-CSS-DATEIEN (OPTIMIERT)
        $css_dir = __DIR__ . '/plugins/';
        $plugin_files_to_load = ['css' => [], 'js' => []]; // Array, um Pfade zu speichern
        
        if (is_dir($css_dir)) {
            // glob() liest Ordner in numerischer/alphabetischer Reihenfolge (10-vor 20-)
            $plugin_dirs = glob($css_dir . '/*', GLOB_ONLYDIR);
            
            foreach ($plugin_dirs as $dir) {
                $full_dir_name = basename($dir); // z.B. "10-cpu"
                // Ermittle den ursprünglichen Namen (z.B. "cpu")
                $clean_plugin_name = get_clean_plugin_name($full_dir_name);
                
                // Prüfe auf die CSS-Datei mit dem BEREINIGTEN Namen
                $css_file = $dir . '/' . $clean_plugin_name . '.css';
                
                if (file_exists($css_file)) {
                    // Speichere den Pfad: plugins/10-cpu/cpu.css
                    $plugin_files_to_load['css'][] = 'plugins/' . $full_dir_name . '/' . $clean_plugin_name . '.css';
                }
            }
            
            // CSS einbinden
            foreach ($plugin_files_to_load['css'] as $relative_path) {
                echo '<link rel="stylesheet" href="' . $relative_path . '">' . "\n";
            }
        }
    ?>

    <script src="script.js"></script>
    
    <?php 
        // 🧭 AUTO-DISCOVERY DER PLUGIN-JS-DATEIEN (OPTIMIERT)
        $js_dir = __DIR__ . '/plugins/';
        if (is_dir($js_dir)) {
            $plugin_dirs = glob($js_dir . '/*', GLOB_ONLYDIR);
            foreach ($plugin_dirs as $dir) {
                $full_dir_name = basename($dir); // z.B. "10-cpu"
                // Ermittle den ursprünglichen Namen (z.B. "cpu")
                $clean_plugin_name = get_clean_plugin_name($full_dir_name);
                
                // Prüfe auf die JS-Datei mit dem BEREINIGTEN Namen
                $js_file = $dir . '/' . $clean_plugin_name . '.js';
                if (file_exists($js_file)) {
                    // Speichere den Pfad: plugins/10-cpu/cpu.js
                    $plugin_files_to_load['js'][] = 'plugins/' . $full_dir_name . '/' . $clean_plugin_name . '.js';
                }
            }
            
            // JS einbinden
            foreach ($plugin_files_to_load['js'] as $relative_path) {
                echo '<script src="' . $relative_path . '"></script>' . "\n";
            }
        }
    ?>

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
        <h1 class="header-title"><a href="https://github.com/YOUR_ANON_USER/hyperjump">hyperjump to</a></h1>

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
                <div class="category-group" style="--category-color: <?= $category['color']; ?>" data-id="<?= $categoryId; ?>" data-category-id="<?= $categoryId; ?>" aria-expanded="true">
                    <h2 class="category-title" data-collapse-toggle>
                        <i class="fa-solid fa-arrows-up-down-left-right drag-handle"></i>
                        <?= $category['title']; ?>
                        <i class="fa-solid fa-chevron-up collapse-icon"></i>
                    </h2>
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
            
            <?php 
                // 🧭 AUTO-DISCOVERY DER WIDGETS (PHP/HTML Markup) (OPTIMIERT)
                $widget_dir = __DIR__ . '/plugins/';
                $found_widgets = [];

                // NEUE FUNKTION zum Durchsuchen von Unterverzeichnissen nach *_widget.php
                function find_widgets_optimized($directory) {
                    $widgets = [];
                    // Scanne Unterverzeichnisse (glob() sortiert sie alphabetisch/numerisch)
                    $dirs = glob($directory . '/*', GLOB_ONLYDIR);
                    
                    foreach ($dirs as $dir) {
                        $full_dir_name = basename($dir);
                        global $categories; // get_clean_plugin_name ist global
                        $clean_plugin_name = get_clean_plugin_name($full_dir_name); // BEREINIGTER Name
                        
                        // SUCHEN nach dem BEREINIGTEN Namen + Suffix
                        $widget_file = $dir . '/' . $clean_plugin_name . '_widget.php';
                        if (file_exists($widget_file)) {
                            // Füge den vollständigen Pfad des Widgets hinzu 
                            $widgets[] = $widget_file; 
                        }
                    }
                    return $widgets;
                }

                if (is_dir($widget_dir)) {
                    $found_widgets = find_widgets_optimized($widget_dir); // Verwende die optimierte Funktion
                }

                if (!empty($found_widgets)) {
                    echo '<h3 class="panel-title widget-section-title"><i class="fa-solid fa-server"></i> System Load Plugins</h3>';
                    foreach ($found_widgets as $widget_path) {
                        include $widget_path; 
                    }
                }
            ?>
            
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

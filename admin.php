<?php
// Session-Prüfung und Daten laden
include 'config.php'; 
include 'data.php';

// Sicherheit: Nur eingeloggte Benutzer dürfen auf diese Seite zugreifen!
if (!IS_LOGGED_IN) {
    header('Location: index.php?login_error=2'); // Leite zur Startseite mit Fehler um
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Links bearbeiten</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="styles2.css">
    <style>
        /* Temporäre Stile für die Admin-Seite */
        body { background-color: var(--main-bg-color); }
        .admin-container { 
            max-width: 1000px; 
            margin: 50px auto; 
            padding: 30px; 
            background: var(--content-bg-color); 
            border-radius: 15px; 
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5); 
            color: var(--text-color);
        }
        h1 { color: var(--accent-color); border-bottom: 2px solid var(--border-color); padding-bottom: 10px; }
        
        /* Neuer Stil für den oberen Rück-Link */
        .top-admin-link {
            display: inline-block;
            margin-bottom: 25px;
            padding: 8px 15px;
            background-color: var(--bar-bg-color);
            border-radius: 5px;
            text-decoration: none;
            color: var(--secondary-text-color);
            border: 1px solid var(--border-color);
            transition: background-color 0.2s, color 0.2s;
        }
        .top-admin-link:hover {
            background-color: var(--border-color);
            color: var(--text-color);
        }
        /* Ende neuer Stil */

        .category-editor { 
            border: 1px solid var(--category-color); 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 8px; 
        }
        .category-editor h2 { color: var(--primary-link-color); margin-top: 0; }
        
        /* NEUE STYLES FÜR KATEGORIE-EINGABEN */
        .category-config {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color-light);
        }

        .category-config label {
            font-weight: bold;
            color: var(--secondary-text-color);
            min-width: 60px;
        }

        .category-config input[type="text"] {
            flex-grow: 1;
        }
        
        /* Angepasstes Grid: 6 Spalten */
        .link-row { 
            display: grid; 
            grid-template-columns: 1fr 2fr 1fr 80px 40px 40px; 
            gap: 10px; 
            margin-bottom: 10px; 
            padding: 8px; 
            border-bottom: 1px dashed var(--border-color);
            align-items: center;
        }

        .link-row:last-child { border-bottom: none; }
        
        .link-row input, .link-row select, .link-row button,
        .category-config input { 
            padding: 5px; 
            border-radius: 4px; 
            border: 1px solid var(--border-color);
            background: var(--bar-bg-color);
            color: var(--text-color);
        }

        .link-row input[type="color"],
        .category-config input[type="color"] { 
            width: 100%; 
            height: 30px; 
            padding: 0; 
            cursor: pointer;
        }

        .link-row .header-row { font-weight: bold; color: var(--secondary-text-color); }
        
        .add-link-btn, .remove-link-btn, .save-button { cursor: pointer; }
        .save-button { 
            display: block; 
            width: 100%; 
            padding: 15px; 
            margin-top: 20px;
            background-color: var(--accent-color); 
            color: var(--main-bg-color); 
            font-weight: bold; 
            border: none; 
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        .save-button:hover { background-color: var(--primary-link-color); }
        
        .admin-link { display: block; text-align: center; margin-top: 20px; }
        .message-box { padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .message-box.success { background-color: rgba(163, 190, 140, 0.2); color: var(--accent-color); }
        .message-box.error { background-color: rgba(255, 85, 85, 0.2); color: var(--color-red); }
    </style>
</head>
<body class="dark-mode"> 
    <div class="admin-container">
        <h1><i class="fa-solid fa-screwdriver-wrench"></i> Admin Dashboard</h1>
        
        <a href="index.php" class="top-admin-link"><i class="fa-solid fa-home"></i> Zurück zum Dashboard</a>
        <?php if (isset($_GET['save_status'])): ?>
            <div class="message-box <?= $_GET['save_status'] == 'success' ? 'success' : 'error'; ?>">
                <?= $_GET['save_status'] == 'success' ? 'Änderungen erfolgreich gespeichert!' : 'FEHLER beim Speichern der Änderungen! Bitte Dateiberechtigungen prüfen.'; ?>
            </div>
        <?php endif; ?>

        <form id="adminForm" action="save_admin.php" method="POST">
            
            <input type="hidden" name="action" value="save_all">

            <?php foreach ($categories as $categoryId => $category): ?>
                <div class="category-editor" data-category-id="<?= $categoryId; ?>" style="--category-color: <?= htmlspecialchars($category['color']); ?>">
                    
                    <h2><?= $category['title']; ?> (ID: <?= $categoryId; ?>)</h2>
                    
                    <div class="category-config">
                        <label for="title-<?= $categoryId; ?>">Titel:</label>
                        <input type="text" 
                               id="title-<?= $categoryId; ?>" 
                               name="categories[<?= $categoryId; ?>][title]" 
                               value="<?= htmlspecialchars($category['title']); ?>" 
                               required>
                        
                        <label for="color-<?= $categoryId; ?>">Farbe:</label>
                        <input type="color" 
                               id="color-<?= $categoryId; ?>" 
                               name="categories[<?= $categoryId; ?>][color]" 
                               value="<?= htmlspecialchars($category['color']); ?>" 
                               title="Kategorie-Grundfarbe">
                    </div>
                    <div class="link-row header-row">
                        <span>Text</span>
                        <span>URL</span>
                        <span>Icon (Fa)</span>
                        <span>Farbe (Bubble)</span>
                        <span>Deaktiviert?</span>
                        <span>Aktion</span>
                    </div>

                    <div id="links-container-<?= $categoryId; ?>">
                        <?php foreach ($category['links'] as $index => $link): 
                            // Der Text des Links wird hier als "Unique ID" verwendet
                            $linkUniqueId = htmlspecialchars($link['text']);
                        ?>
                            <div class="link-row" data-id="<?= $index; ?>">
                                <input type="text" name="categories[<?= $categoryId; ?>][links][<?= $index; ?>][text]" value="<?= htmlspecialchars($link['text']); ?>" required>
                                
                                <input type="url" name="categories[<?= $categoryId; ?>][links][<?= $index; ?>][url]" value="<?= htmlspecialchars($link['url']); ?>" required>
                                
                                <input type="text" name="categories[<?= $categoryId; ?>][links][<?= $index; ?>][icon]" value="<?= htmlspecialchars($link['icon']); ?>">
                                
                                <input type="color" name="categories[<?= $categoryId; ?>][links][<?= $index; ?>][color]" value="<?= htmlspecialchars($link['color'] ?? $category['color']); ?>" title="Individuelle Bubble-Farbe">
                                
                                <input type="checkbox" name="categories[<?= $categoryId; ?>][links][<?= $index; ?>][disabled]" value="true" <?= isset($link['disabled']) && $link['disabled'] ? 'checked' : ''; ?>>
                                
                                <button type="button" class="remove-link-btn" onclick="removeLink(this)"><i class="fa-solid fa-trash-alt"></i></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" class="add-link-btn" onclick="addLink('<?= $categoryId; ?>')"><i class="fa-solid fa-plus"></i> Link hinzufügen</button>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="save-button"><i class="fa-solid fa-save"></i> Alle Änderungen Speichern und data.php Umschreiben</button>
        </form>
        
        <a href="index.php" class="admin-link"><i class="fa-solid fa-home"></i> Zurück zum Dashboard</a>
        <a href="login.php?logout=true" class="admin-link"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>

    </div>

    <script>
        let linkIndexCounter = 1000; // Counter für neue Links, um temporäre IDs zu gewährleisten

        function getNewIndex() {
            return linkIndexCounter++;
        }

        function addLink(categoryId) {
            const container = document.getElementById(`links-container-${categoryId}`);
            const newIndex = getNewIndex();
            
            // Finde die aktuelle Kategorie-Farbe, um sie als Standard für den neuen Bubble zu verwenden
            const categoryEditor = document.querySelector(`.category-editor[data-category-id="${categoryId}"]`);
            const defaultCategoryColor = categoryEditor.querySelector(`input[name="categories[${categoryId}][color]"]`).value;

            const newRow = document.createElement('div');
            newRow.className = 'link-row new-link';
            newRow.setAttribute('data-id', newIndex);

            newRow.innerHTML = `
                <input type="text" name="categories[${categoryId}][links][${newIndex}][text]" value="" placeholder="Neuer Link Name" required>
                <input type="url" name="categories[${categoryId}][links][${newIndex}][url]" value="" placeholder="https://..." required>
                <input type="text" name="categories[${categoryId}][links][${newIndex}][icon]" value="link" placeholder="Icon Name">
                <input type="color" name="categories[${categoryId}][links][${newIndex}][color]" value="${defaultCategoryColor}" title="Individuelle Bubble-Farbe">
                <input type="checkbox" name="categories[${categoryId}][links][${newIndex}][disabled]" value="true">
                <button type="button" class="remove-link-btn" onclick="removeLink(this)"><i class="fa-solid fa-trash-alt"></i></button>
            `;
            container.appendChild(newRow);
        }

        function removeLink(button) {
            // Bestätigung im Admin-Panel ist OK, da es eine einmalige Aktion ist, die zur Sicherheit dient
            if (confirm("Diesen Link wirklich entfernen? Die Änderung wird erst nach dem Speichern permanent.")) {
                const row = button.closest('.link-row');
                row.remove();
            }
        }

        // Event-Listener für dynamische Farbanpassung der Box-Border
        document.addEventListener('input', function(e) {
            if (e.target.matches('input[name$="[color]"][title="Kategorie-Grundfarbe"]')) {
                const color = e.target.value;
                const categoryEditor = e.target.closest('.category-editor');
                if (categoryEditor) {
                    // Aktualisiert die CSS-Variable für die Border
                    categoryEditor.style.setProperty('--category-color', color);
                }
            }
        });
    </script>
</body>
</html>

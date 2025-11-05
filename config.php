<?php
// --- SICHERHEITS-KONFIGURATION ---

// 1. Session starten (muss immer am Anfang der Datei stehen)
session_start();

// 2. Erstellen Sie den Hash Ihres Passworts (EINMALIG!)
// Gehen Sie in Ihren Browser und rufen Sie eine leere PHP-Datei mit folgendem Inhalt auf, 
// um den Hash zu generieren. Kopieren Sie den resultierenden langen String hierher:
// echo password_hash("MEIN_SICHERES_PASSWORT", PASSWORD_DEFAULT); 
// Beispiel: $2y$10$asdfaDfasdfasdfasdfasdfasdf...

define('ADMIN_PASSWORD_HASH', 'HIER-DAS-GESALTETE-PASSORT-VON-DER-GENERATOR.PHP-REIN'); // Platzhalter: BITTE ERSETZEN!

// 3. Statusprüfung: Wird in der Session gespeichert, wenn der Login erfolgreich war
define('IS_LOGGED_IN', isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true);
?>

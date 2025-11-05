<?php
// MUSS config.php laden, um Session zu starten und den ADMIN_PASSWORD_HASH zu bekommen
include 'config.php'; 

// 1. Logout-Funktion
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    // Session-Variablen löschen
    session_unset();
    // Session zerstören
    session_destroy();
    
    // Umleiten zur Startseite
    header('Location: index.php');
    exit;
}

// 2. Login-Verarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password_input = $_POST['password'];

    // Passwort mit dem Hash verifizieren (sicherer Weg!)
    if (password_verify($password_input, ADMIN_PASSWORD_HASH)) {
        // Erfolg: Session-Variable setzen
        $_SESSION['admin_logged_in'] = true;
        
        // Umleiten zur Startseite
        header('Location: index.php');
        exit;
    } else {
        // Fehler: Session-Variable löschen und mit Fehlermeldung umleiten
        unset($_SESSION['admin_logged_in']);
        header('Location: index.php?login_error=1');
        exit;
    }
}

// Falls jemand die login.php direkt ohne POST/Logout aufruft
header('Location: index.php');
exit;
?>

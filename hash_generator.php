<?php
$password = "HIERDASPASSWORTREINDASGESALTETWERDENSOLL"; // <--- ERSETZEN SIE DIES
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "Ihr gehashtes Passwort lautet:<br>";
echo "<strong>" . $hashed_password . "</strong>";
?>

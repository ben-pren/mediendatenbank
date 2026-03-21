<?php
/* Prüfen, ob scon eine Session läuft, neue gestartet wird*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Prüfen, ob Nutzer eingeloggt ist */
if (!isset($_SESSION["NutzerID"])) {
    header("Location: ../pages/login.php");
    exit();
}

?>
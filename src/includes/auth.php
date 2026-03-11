<?php
session_start();

/* Prüfen ob Nutzer eingeloggt ist */

if (!isset($_SESSION["NutzerID"])) {
        header("Location: ../pages/login.php");
    exit();
}

?>
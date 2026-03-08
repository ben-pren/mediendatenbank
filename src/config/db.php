<?php

/* Datenbank über xampp */
$host = "localhost";
$dbname = "mediendb";
/* Standarduser */
$username = "root";
$password = "";

/* Datenbankverbindung */
$connection = new mysqli($host, $username, $password, $dbname);

/* Fehlermeldung bei Verbinung */
if ($connection ->connect_error) {
die("Verbindung fehlgeschlagen: ". $connection
->connect_error);
}

?>
<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['NutzerID']) && isset($_POST['RequestedTagName'])) {
    $nutzerID = $_SESSION['NutzerID'];
    $requestedTagName = $_POST['RequestedTagName'];
    $kommentar = $_POST['Kommentar'] ?? null;

    $stmt = $connection->prepare(
        "INSERT INTO Tag_Request (NutzerID, RequestedTagName, Kommentar, Status) VALUES (?, ?, ?, 'offen')"
    );
    
    $stmt->bind_param("iss", $nutzerID, $requestedTagName, $kommentar);
    
    if ($stmt->execute()) {
        // Redirect zurück zur Startseite (src/pages/index.php)
        echo "<script>alert('Vielen Dank! Deine Anfrage wurde gesendet.'); window.location.href='../../src/pages/index.php';</script>";
    } else {
        echo "Fehler beim Senden: " . $connection->error;
    }
}

$connection->close();
?>

<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['NutzerID'])) {
    $nutzerID = $_SESSION['NutzerID'];
    $requestedTagName = $_POST['RequestedTagName'];
    $kommentar = $_POST['Kommentar'] ?? null;
    $mediumID = (!empty($_POST['MediumID']) && $_POST['MediumID'] != 0) ? $_POST['MediumID'] : null;

    $stmt = $connection->prepare(
        "INSERT INTO Tag_Request (NutzerID, MediumID, RequestedTagName, Kommentar, Status) VALUES (?, ?, ?, ?, 'offen')"
    );
    
    $stmt->bind_param("iiss", $nutzerID, $mediumID, $requestedTagName, $kommentar);
    
    if ($stmt->execute()) {
        // Redirect zurück zur Startseite (public/index.php)
        echo "<script>alert('Vielen Dank! Deine Anfrage wurde gesendet.'); window.location.href='../../public/index.php';</script>";
    } else {
        echo "Fehler beim Senden: " . $connection->error;
    }
}
?>
<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pfad zur Datenbank
require_once '../config/db.php'; 

if (!isset($_SESSION['NutzerID'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Tag anfragen</title>
    <link rel="stylesheet" type="text/css" href="../../public/css/style.css">
</head>
<body class="beige-background">

    <?php include '../includes/header.php'; ?>

    <div class="content_container" style="padding: 50px; text-align: center;">
        <h2>Neuen Tag anfragen</h2>
        <p>Du vermisst ein Schlagwort? Schick uns einen Vorschlag!</p>
        
        <form action="tag_request_db.php" method="POST" class="standard_form" style="display: inline-block; text-align: left;">
            <input type="hidden" name="MediumID" value="0"> 
            
            <label for="RequestedTagName">Gewünschter Tag:</label><br>
            <input type="text" name="RequestedTagName" placeholder="z.B. Weltall" required style="width: 300px; padding: 10px; margin-top: 5px;"><br><br>
            
            <label for="Kommentar">Kommentar (optional):</label><br>
            <textarea name="Kommentar" rows="4" style="width: 300px; padding: 10px; margin-top: 5px;"></textarea><br><br>
            
            <button type="submit" class="login_button" style="width: 100%; padding: 10px; cursor: pointer;">Anfrage senden</button>
        </form>
    </div>
</body>
</html>
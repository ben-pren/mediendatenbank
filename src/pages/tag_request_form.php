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
    <link rel="stylesheet" type="text/css" href="../../public/css/tag_request_form.css">
</head>
<body>
  <header>
    <?php include '../includes/header.php'; ?>
  </header>
    <?php include __DIR__ . '/../includes/background.php'; ?>
	
    <div class="content_container">
      <h2>Neuen Tag anfragen</h2>
      <p>Du vermisst ein Schlagwort? Schick uns einen Vorschlag!</p>
        
      <form action="tag_request_db.php" method="POST">
            
        <label for="RequestedTagName">Gewünschter Tag:</label>
        <input type="text" name="RequestedTagName" id="RequestedTagName" placeholder="z.B. Weltall" required style="width: 300px; padding: 10px; margin-top: 5px;">
            
        <label for="Kommentar">Kommentar (optional):</label>
        <textarea name="Kommentar" id="Kommentar" rows="6" maxlength="500"></textarea>
            
        <button type="submit">Anfrage senden</button>
      </form>
    </div>
    
  <footer>
    <?php include  __DIR__ . '/../includes/footer.php'; ?>
  </footer>	  
</body>
</html>

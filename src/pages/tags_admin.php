<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once '../includes/auth.php'; 
require_once '../config/db.php';

if ($_SESSION['Rolle'] !== 'Admin') {
    die("Zugriff verweigert.");
}

// akzeptieren
if (isset($_POST['accept_request'])) {
    $requestID = $_POST['request_id'];
    $tag_titel = $_POST['tag_name'];
    $tag_titel_compare = strtolower(preg_replace('/\s+/', '', $tag_titel));// keine lehrzeichen und alles klein für eindeutigeren Vergleich

    $connection->begin_transaction();
    try {
        // Prüfen, ob Tag schon existiert
        $sql = "SELECT TagName FROM tag";
        $result = mysqli_query($connection,$sql);
        $doppeltag  = false;
        //checkt für doppelte tags und setzt Anfrage auf abgelehnt falls Tag schon vorhanden
        if($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $comparedb = strtolower(preg_replace('/\s+/', '', $row['TagName']));
                if($comparedb === $tag_titel_compare) {
                    $doppeltag = true;
                    
                    $stmt = $connection->prepare("UPDATE Tag_Request SET Status = 'abgelehnt' WHERE RequestID = ?");
                    $stmt->bind_param("i", $requestID);
                    
                    if ($stmt->execute()) {
                        $connection->commit();
                        $_SESSION['anfrage_erfolg'] = "Anfrage abgelehnt! Bereits Tag mit gleichem Namen vorhanden!";
                        header("Location: tags_admin.php");
                        exit();
                    } else {
                        echo "Fehler beim Ablehnen: " . $stmt->error;
                    }
                }
            }
        }//  Tag existiert nicht -> dann neu anlegen
          if (!$doppeltag) {
            $stmt1 = $connection->prepare("INSERT INTO Tag (TagName) VALUES (?)");
            $stmt1->bind_param("s", $tag_titel);
            $stmt1->execute();
            
            $stmt3 = $connection->prepare("UPDATE Tag_Request SET Status = 'genehmigt' WHERE RequestID = ?");
            $stmt3->bind_param("i", $requestID);
            $stmt3->execute();
            
            $connection->commit();
            $_SESSION['anfrage_erfolg'] = "Aktion erfolgreich ausgeführt!";
            header("Location: tags_admin.php");
            exit();
          }

    } catch (Exception $e) {
        $connection->rollback();
        echo "Fehler: " . $e->getMessage();
    }
}

// ablehnen 
if (isset($_POST['reject_request'])) {
    $requestID = $_POST['request_id'];
    
    $stmt = $connection->prepare("UPDATE Tag_Request SET Status = 'abgelehnt' WHERE RequestID = ?");
    $stmt->bind_param("i", $requestID);
    
    if ($stmt->execute()) {
        $_SESSION['anfrage_erfolg'] = "Anfrage abgelehnt!";
        header("Location: tags_admin.php");
        exit();
    } else {
        echo "Fehler beim Ablehnen: " . $stmt->error;
    }
}


// abfrage
$requests = $connection->query("SELECT tr.*, n.Benutzername 
                                FROM Tag_Request tr 
                                JOIN Nutzer n ON tr.NutzerID = n.NutzerID 
                                WHERE tr.Status = 'offen'");
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Tag-Anfragen verwalten</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/tag_admin.css">
</head>
<body>
  <header>
    <?php include '../includes/header.php'; ?>
  </header>
  <main>
   
      <h2>Offene Tag-Anfragen</h2>
	  <p class="erfolgsmeldung"> 
      <?php if (isset($_SESSION['anfrage_erfolg'])) {
               printf($_SESSION['anfrage_erfolg']);
               unset($_SESSION['anfrage_erfolg']);
            }?>
            &nbsp;
	  </p>
	  
        <div class="contaiener_request_head">
          <div>Nutzer</div>
          <div>Gewünschter Tag</div>
          <div class="kommentar">Kommentar</div>
          <div>Erstellungszeitpunkt</div>
          <div>Aktion</div>
        </div>
         
          <?php while ($row = $requests->fetch_assoc()): ?>
            
              <div class="container_request_body">
                <div><?php echo htmlspecialchars($row['Benutzername']); ?></div>
                <div><?php echo htmlspecialchars($row['RequestedTagName']); ?></div>
                <div class="kommentar"><?php echo htmlspecialchars($row['Kommentar']); ?></div>
                <div><?php echo htmlspecialchars($row['ErstelltAm']); ?></div>
              
                <div class="container_buttons">
                <form method="POST">
                   <input type="hidden" name="request_id" value="<?php echo $row['RequestID']; ?>">
                   <input type="hidden" name="tag_name" value="<?php echo $row['RequestedTagName']; ?>">
                   <button type="submit" name="accept_request" class="yes_button buttons">Akzeptieren</button>
                </form>
                <form method="POST">
                   <input type="hidden" name="request_id" value="<?php echo $row['RequestID']; ?>">
                   <button type="submit" name="reject_request" class="no_button buttons" onclick="return confirm('Wirklich ablehnen?')">Ablehnen</button>
                </form>
                </div>
			  </div>
            
                <?php endwhile; ?>
            <?php if ($requests->num_rows === 0): ?>
                <p class="center">Keine offenen Anfragen vorhanden.</p>
            <?php endif; ?>

  </main>
    
  <footer>
    <?php include  __DIR__ . '/../includes/footer.php'; ?>
  </footer>	
</body>
</html>
<?php 
$connection->close();
?>

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
    $admin_comment = $_POST['kommentar_admin'];
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
                    $admin_comment = "Ähnlicher Tag bereits vorhanden";
                    
                    $stmt = $connection->prepare("UPDATE tag_request SET Status = 'abgelehnt', Kommentar_Admin = ? WHERE RequestID = ?");
                    $stmt->bind_param("si", $admin_comment, $requestID);
                    
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
            
            $stmt3 = $connection->prepare("UPDATE Tag_Request SET Status = 'genehmigt', Kommentar_Admin = ? WHERE RequestID = ?");
            $stmt3->bind_param("si", $admin_comment, $requestID);
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


if (isset($_POST['accept_request_change'])) {
    $admin_comment = $_POST['kommentar_admin'];
    $requestID = $_POST['request_id'];
    $tag_titel = $_POST['tag_name'];
    $tag_id = $_POST['tag_id'];
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
                    $admin_comment = "Ähnlicher Tag bereits vorhanden";
                    
                    $stmt = $connection->prepare("UPDATE tag_request SET Status = 'abgelehnt', Kommentar_Admin = ? WHERE RequestID = ?");
                    $stmt->bind_param("si", $admin_comment, $requestID);
                    
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
        }//  Tag existiert nicht -> dann ändern
        if (!$doppeltag) {
            $stmt1 = $connection->prepare("UPDATE Tag SET TagName = ? WHERE TagID = ?");
            $stmt1->bind_param("si", $tag_titel, $tag_id);
            $stmt1->execute();
            
            $stmt3 = $connection->prepare("UPDATE Tag_Request SET Status = 'genehmigt', Kommentar_Admin = ? WHERE RequestID = ?");
            $stmt3->bind_param("si", $admin_comment, $requestID);
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
    $admin_comment = $_POST['kommentar_admin'];
    
    $stmt = $connection->prepare("UPDATE tag_request SET Status = 'abgelehnt', Kommentar_Admin = ? WHERE RequestID = ?");
    $stmt->bind_param("si", $admin_comment, $requestID);
    
    if ($stmt->execute()) {
        $_SESSION['anfrage_erfolg'] = "Anfrage abgelehnt!";
        header("Location: tags_admin.php");
        exit();
    } else {
        echo "Fehler beim Ablehnen: " . $stmt->error;
    }
}

if (isset($_POST['reject_request'])) {
    $requestID = $_POST['request_id'];
    $admin_comment = $_POST['kommentar_admin'];
    
    $stmt = $connection->prepare("UPDATE tag_request SET Status = 'abgelehnt', Kommentar_Admin = ? WHERE RequestID = ?");
    $stmt->bind_param("si", $admin_comment, $requestID);
    
    if ($stmt->execute()) {
        $_SESSION['anfrage_erfolg'] = "Anfrage abgelehnt!";
        header("Location: tags_admin.php");
        exit();
    } else {
        echo "Fehler beim Ablehnen: " . $stmt->error;
    }
}




// abfrage
$requests = $connection->query("SELECT tr.*, n.Benutzername, t.TagName
                                FROM Tag_Request tr
                                JOIN Nutzer n ON tr.NutzerID = n.NutzerID
                                LEFT JOIN Tag t ON tr.TagID = t.TagID
                                WHERE tr.Status = 'offen'");

$alleRequests = [];
while ($row = $requests->fetch_assoc()) {
    $alleRequests[] = $row;
}


$neue_Tags = array_filter($alleRequests, function($row) {
    return is_null($row['TagID']);
});

$tags_Aendern = array_filter($alleRequests, function($row) {
    return !is_null($row['TagID']);
});

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
	<section>   
	  <h3>Neue Tags</h3>
        <div class="contaiener_request_head">
          <div>Nutzer</div>
          <div>Gewünschter Tag</div>
          <div class="kommentar">Kommentar</div>
          <div class="kommentar">Admin Kommentar</div>
          <div>Erstellungszeitpunkt</div>
          <div>Aktion</div>
        </div>
         
          <?php if (count($neue_Tags) > 0): ?>
            <?php foreach ($neue_Tags as $row): ?>
              <div class="container_request_body">
                <div><?php echo htmlspecialchars($row['Benutzername']); ?></div>
                <div><?php echo htmlspecialchars($row['RequestedTagName']); ?></div>
                <div class="kommentar"><?php echo htmlspecialchars($row['Kommentar']); ?></div>
                <div class="kommentar">
                  <form action="" method="POST">
                    <textarea name="kommentar_admin" id="kommentar_admin_<?php echo $row['RequestID']; ?>" rows="2" maxlength="500"><?php echo $row['Kommentar_Admin']; ?></textarea>
                  </form>
                </div>
                <div><?php echo htmlspecialchars($row['ErstelltAm']); ?></div>
              
                <div class="container_buttons">
                <form action="" method="POST">
                   <input type="hidden" name="request_id" value="<?php echo $row['RequestID']; ?>">
                   <input type="hidden" name="tag_name" value="<?php echo $row['RequestedTagName']; ?>">
                   <input type="hidden" name="kommentar_admin" value="" id="hidden_kommentar_<?php echo $row['RequestID']; ?>">
                   <button type="submit" name="accept_request" class="yes_button buttons" onclick="setCommentAccept(<?php echo $row['RequestID']; ?>)">Akzeptieren</button>
                   <button type="submit" name="reject_request" class="no_button buttons" onclick="return setCommentReject(<?php echo $row['RequestID']; ?>)">Ablehnen</button>
                </form>
                </div>
			  </div>
            <?php endforeach; ?> 	
          <?php else: ?>
      		<p class="center">Keine neuen Tag-Anfragen vorhanden.</p>  
          <?php endif;?>
    </section>       
            
    <section>   
      <h3>Tags Ändern</h3>      
        <div class="contaiener_request_head">
          <div>Nutzer</div>
          <div>Alter Tag</div>
          <div>Gewünschter Tag</div>
          <div class="kommentar">Kommentar</div>
          <div class="kommentar">Admin Kommentar</div>
          <div>Erstellungszeitpunkt</div>
          <div>Aktion</div>
        </div>    

          <?php if (count($tags_Aendern) > 0): ?>
            <?php foreach ($tags_Aendern as $row): ?>
            
              <div class="container_request_body">
                <div><?php echo htmlspecialchars($row['Benutzername']); ?></div>
                <div><?php echo htmlspecialchars($row['TagName']);?></div>
                <div><?php echo htmlspecialchars($row['RequestedTagName']); ?></div>
                <div class="kommentar"><?php echo htmlspecialchars($row['Kommentar']); ?></div>
                <div class="kommentar">
                  <form action="" method="POST">
                    <textarea name="kommentar_admin" id="kommentar_admin_<?php echo $row['RequestID']; ?>" rows="2" maxlength="500"><?php echo $row['Kommentar_Admin']; ?></textarea>
                  </form>
                </div>
                <div><?php echo htmlspecialchars($row['ErstelltAm']); ?></div>
              
                <div class="container_buttons">
                <form action="" method="POST">
                   <input type="hidden" name="request_id" value="<?php echo $row['RequestID']; ?>">
                   <input type="hidden" name="tag_name" value="<?php echo $row['RequestedTagName']; ?>">
                   <input type="hidden" name="tag_id" value="<?php echo $row['TagID']; ?>">
                   <input type="hidden" name="kommentar_admin" value="" id="hidden_kommentar_<?php echo $row['RequestID']; ?>">
                   <button type="submit" name="accept_request_change" class="yes_button buttons" onclick="setCommentAccept(<?php echo $row['RequestID']; ?>)">Akzeptieren</button>
                   <button type="submit" name="reject_request" class="no_button buttons" onclick="return setCommentReject(<?php echo $row['RequestID']; ?>)">Ablehnen</button>
                </form>
                </div>
			  </div>
            <?php endforeach; ?>
          <?php else: ?>
           <p class="center">Keine Tag-Änderungs-Anfragen vorhanden.</p>
          <?php endif; ?>
    </section>         
            
  </main>
    
  <footer>
    <?php include  __DIR__ . '/../includes/footer.php'; ?>
  </footer>
  
  <script>
function setCommentAccept(requestId) {
    const commentTextarea = document.getElementById('kommentar_admin_' + requestId);
    const hiddenInput = document.getElementById('hidden_kommentar_' + requestId);
    hiddenInput.value = commentTextarea.value;
    return true;
}

function setCommentReject(requestId) {
    const commentTextarea = document.getElementById('kommentar_admin_' + requestId);
    const hiddenInput = document.getElementById('hidden_kommentar_' + requestId);
    hiddenInput.value = commentTextarea.value;
    return confirm('Wirklich ablehnen?');
}
</script>	
</body>
</html>
<?php 
$connection->close();
?>

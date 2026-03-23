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
                    $_SESSION['anfrage_erfolg'] = "Bereits Tag mit gleichem Namen vorhanden!";
                    
                    $stmt = $connection->prepare("UPDATE Tag_Request SET Status = 'abgelehnt' WHERE RequestID = ?");
                    $stmt->bind_param("i", $requestID);
                    
                    if ($stmt->execute()) {
                        $connection->commit();
                        header("Location: tags_admin.php?success=2");
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
            header("Location: tags_admin.php?success=1");
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
        header("Location: tags_admin.php?success=2");
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
</head>
<body>
  <header>
    <?php include '../includes/header.php'; ?>
  </header>
  <main>
    <div class="content_container" style="padding: 10px; margin-top: 20px;">
      <h2>Offene Tag-Anfragen</h2>

      <?php if (isset($_GET['success']) && ($_GET['success'] === "1")): ?>
            <p style="color: green;">Aktion erfolgreich ausgeführt!</p>
      <?php endif; ?>
        
      <?php if (isset($_GET['success']) && ($_GET['success'] === "2")): ?>
            <p style="color: red;">Anfrage abgelehnt!</p>
      <?php endif; ?>

      <table border="1" style="width: 100%; border-collapse: collapse; background: white;">
        <thead>
          <tr style="background: #eee;">
            <th>Nutzer</th>
            <th>Gewünschter Tag</th>
            <th>Kommentar</th>
            <th>Aktion</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $requests->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['Benutzername']); ?></td>
              <td><?php echo htmlspecialchars($row['RequestedTagName']); ?></td>
              <td><?php echo htmlspecialchars($row['Kommentar']); ?></td>
              <td><?php echo !empty($row['Titel']) ? htmlspecialchars($row['Titel']) : "<i>Allgemeine Anfrage</i>"; ?></td>
              <td>
                <form method="POST" style="display:inline;">
                   <input type="hidden" name="request_id" value="<?php echo $row['RequestID']; ?>">
                   <input type="hidden" name="tag_name" value="<?php echo $row['RequestedTagName']; ?>">
                   <button type="submit" name="accept_request" style="background: green; color: white; cursor:pointer;">Akzeptieren</button>
                </form>
                <form method="POST" style="display:inline;">
                   <input type="hidden" name="request_id" value="<?php echo $row['RequestID']; ?>">
                   <button type="submit" name="reject_request" style="background: red; color: white; border: none; padding: 5px 10px; cursor:pointer;" onclick="return confirm('Wirklich ablehnen?')">Ablehnen</button>
                </form>
                 
              </td>
            </tr>
                <?php endwhile; ?>

            <?php if ($requests->num_rows === 0): ?>
                <tr><td colspan="5" style="text-align:center;">Keine offenen Anfragen vorhanden.</td></tr>
            <?php endif; ?>
            <?php if (isset($_SESSION['anfrage_erfolg'])) {
                    echo $_SESSION['anfrage_erfolg'];
                    unset($_SESSION['anfrage_erfolg']);
                  }?>
        </tbody>
      </table>
    </div>
  </main>
    
  <footer>
    <?php include  __DIR__ . '/../includes/footer.php'; ?>
  </footer>	
</body>
</html>

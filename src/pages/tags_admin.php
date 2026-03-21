<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/auth.php'; 
require_once '../config/db.php';

if ($_SESSION['Rolle'] !== 'Admin') {
    die("Zugriff verweigert.");
}

// akzeptieren
if (isset($_POST['accept_request'])) {
    $requestID = $_POST['request_id'];
    $tagName = trim($_POST['tag_name']); // trim entfernt unnötige Leerzeichen
    $mediumID = !empty($_POST['medium_id']) ? (int)$_POST['medium_id'] : 0;

    $connection->begin_transaction();
    try {
        // Prüfen, ob Tag schon existiert
        $checkTag = $connection->prepare("SELECT TagID FROM Tag WHERE TagName = ?");
        $checkTag->bind_param("s", $tagName);
        $checkTag->execute();
        $result = $checkTag->get_result();
        
        if ($result->num_rows > 0) {
            $tagRow = $result->fetch_assoc();
            $tagID = $tagRow['TagID'];
        } else {
            //  Tag xistiert nicht -> dann neu anlegen
            $stmt1 = $connection->prepare("INSERT INTO Tag (TagName) VALUES (?)");
            $stmt1->bind_param("s", $tagName);
            $stmt1->execute();
            $tagID = $connection->insert_id;
        }

        // verknüpfen, wenn MediumID vorhanden ist
        if ($mediumID > 0) {
            $stmt2 = $connection->prepare("INSERT IGNORE INTO Medium_has_Tag (MediumID, TagID) VALUES (?, ?)");
            $stmt2->bind_param("ii", $mediumID, $tagID);
            $stmt2->execute();
        }

        // Request als akzeptiert markieren
        $stmt3 = $connection->prepare("UPDATE Tag_Request SET Status = 'genehmigt' WHERE RequestID = ?");
        $stmt3->bind_param("i", $requestID);
        $stmt3->execute();

        $connection->commit();
        header("Location: tags_admin.php?success=1");
        exit();
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


// abfrage mit left join
$requests = $connection->query("SELECT tr.*, n.Benutzername, m.Titel 
                                FROM Tag_Request tr 
                                JOIN Nutzer n ON tr.NutzerID = n.NutzerID 
                                LEFT JOIN Medium m ON tr.MediumID = m.MediumID 
                                WHERE tr.Status = 'offen'");
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Tag-Anfragen verwalten</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body class="beige-background">
    <?php include '../includes/header.php'; ?>

    <div class="content_container" style="padding: 10px; margin-top: -20px;">
        <h2>Offene Tag-Anfragen</h2>

        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Aktion erfolgreich ausgeführt!</p>
        <?php endif; ?>

        <table border="1" style="width: 100%; border-collapse: collapse; background: white;">
            <thead>
                <tr style="background: #eee;">
                    <th>Nutzer</th>
                    <th>Medium (Titel)</th>
                    <th>Gewünschter Tag</th>
                    <th>Kommentar</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $requests->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Benutzername']); ?></td>
                        <td><?php echo !empty($row['Titel']) ? htmlspecialchars($row['Titel']) : "<i>Allgemeine Anfrage</i>"; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['RequestedTagName']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['Kommentar']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row['RequestID']; ?>">
                                <input type="hidden" name="tag_name" value="<?php echo $row['RequestedTagName']; ?>">
                                <input type="hidden" name="medium_id" value="<?php echo $row['MediumID']; ?>">
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
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";

/* Zur Galerie Weiterleitung bei fehlender id */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gallery.php");
    exit();
}

$id = (int) $_GET['id'];
$erfolg = "";

/* Medium aus der Datenbank laden */
$sql = "SELECT m.MediumID, m.Titel, m.Medienart, m.Datentyp, m.Groesse, m.Path, m.NutzerID, n.Benutzername
        FROM Medium m
        JOIN Nutzer n ON m.NutzerID = n.NutzerID
        WHERE MediumID = $id";
$result = mysqli_query($connection, $sql);
$medium = $result->fetch_assoc();

/* Medium exisstiert nicht */
if (!$medium) {
    header("Location: gallery.php");
    exit();
}

/* Besitzer oder Admin */
if(isset($_SESSION["Benutzername"])) {
   $is_owner = $_SESSION['NutzerID'] == $medium['NutzerID']
         || $_SESSION['Rolle'] === 'Admin';
}

/* Löschen */
if (isset($_POST['loeschen']) && $is_owner) {
    $file_path = str_replace(
        "http://localhost/MedienDB/src/",
        $_SERVER['DOCUMENT_ROOT'] . "/MedienDB/src/",
        $medium['Path']
    );
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    /* Tags und Medium aus DB entfernen */
    mysqli_query($connection, "DELETE FROM Medium_has_Tag WHERE MediumID = $id");
    mysqli_query($connection, "DELETE FROM Medium WHERE MediumID = $id");

    $connection->close();
    header("Location: gallery.php");
    exit();
}

// Änderung von Titel und Tags
if (isset($_POST['speichern']) && $is_owner && !empty($_POST['tags'])) {
    /* Neuer Name speichern */
    if ($medium['Titel'] != $_POST['titel']) {
        $titel = ($_POST['titel']);
        mysqli_query($connection, "UPDATE Medium SET Titel = '$titel' WHERE MediumID = $id");
        $medium['Titel'] = $_POST['titel'];
        $erfolg = "Änderungen gespeichert.";
    } 
    /* Neuer Tags speichern */
    $tags_new = $_POST['tags'];
    $connection->begin_transaction(); 
    
    try {
        mysqli_query($connection, "DELETE FROM Medium_has_Tag WHERE MediumID = $id");
        
        foreach ($tags_new as $tag_id) {
            mysqli_query($connection,"INSERT INTO Medium_has_Tag (MediumID, TagID) VALUES ($id, $tag_id)");
        }
        
        $connection->commit();
        $erfolg = "Änderungen gespeichert.";
        
    } catch (Exception $e) {
        $connection->rollback();
        $erfolg = "Fehler beim Speichern der Tags.";
    }
}

/* Tags des Mediums laden */
$tags_medium = [];
$tag_result = mysqli_query($connection,
    "SELECT t.TagName, t.TagID FROM Tag t
     JOIN Medium_has_Tag mht ON t.TagID = mht.TagID
     WHERE mht.MediumID = $id"
);
while ($row = $tag_result->fetch_assoc()) {
    $tags_medium[$row['TagID']] = $row['TagName'];
}

/* Alle Tags laden*/
$tags_all = [];
$tag_result_all = mysqli_query($connection, "SELECT TagID, TagName FROM Tag ORDER BY TagName");
while ($row = $tag_result_all->fetch_assoc()) {
    $tags_all[] = $row;
}





/* Bearbeitung */
$modus = (isset($_POST['bearbeiten']) || isset($_GET['bearbeiten'])) ? 'edit' : 'view';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mediendetails</title>
    <link rel="stylesheet" type="text/css" href="../../public/css/style.css">
    <link rel="stylesheet" type="text/css" href="../../public/css/media_details.css">
</head>
<body>
    <header>
        <?php include __DIR__ . '/../../src/includes/header.php'; ?>
    </header>
    <?php include __DIR__ . '/../includes/background.php'; ?>

    <section class="gallery_section">
        <div class="media_details">
            <?php if (isset($_SESSION['NutzerID']) && $is_owner && $modus !== 'edit'): ?>
            <a href="media_details.php?id=<?php echo $id; ?>&bearbeiten=1"
                class="edit_top_button">
                <img src="../../public/icons/einstellungen.svg" alt="Bearbeiten">
            </a>
            <?php endif; ?>
            <a href="gallery.php" class="close_button">
                <img src="../../public/icons/schliessen.svg" alt="Zurück">
            </a>

            <!-- HTML-Element abhängig von Medienart -->
			<?php
			$medienart = $medium['Medienart'];
			$path      = htmlspecialchars($medium['Path']);

			if ($medienart === 'Bild') {
			echo "<div style='display:flex; flex-direction:column; gap:8px;'>
                    <img class='media' src='$path' alt='Vorschau'>
                    <a href='$path' target='_blank' style='text-align:center; font-size:16px;' class='link'>Vollansicht öffnen</a>
                  </div>";
    		    

			} elseif ($medienart === 'Video') {
    		echo "
        		<video class='media' controls>
            		<source src='$path' type='video/mp4'>
        		</video>
    		";

			} elseif ($medienart === 'Hoerbuch') {
    		echo "
        		<div class='media media_audio'>
                    <img src='../../public/icons/hoerbuch.svg' alt='Hörbuch' class='audio_icon'>
            		<audio controls>
                		<source src='$path' type='audio/mpeg'>
            		</audio>
        		</div>
    		";

			} elseif ($medienart === 'eBook') {
                /* Mit Link zur Vollansicht */
    		echo "
                <div style='display:flex; flex-direction:column; gap:8px;'>
                    <iframe class='media' src='$path' title='eBook-Vorschau'></iframe>
                    <a href='$path' target='_blank' style='text-align:center; font-size:16px;' class='link'>Vollansicht öffnen </a>
                </div>
    		";
			}
			?>

            <div class="media_info">

                <?php if ($erfolg): ?>
                    <p class="media_erfolg"><?php echo $erfolg; ?></p>
                <?php endif; ?>

                <?php if ($modus === 'edit' && $is_owner): ?>

                    <!-- Bearbeitungsformular -->
                    <form action="media_details.php?id=<?php echo $id; ?>" method="post">

                        <div class="media_info_row">
                            <span class="media_label">Titel</span>
                            <input type="text" name="titel"
                                   value="<?php echo htmlspecialchars($medium['Titel']); ?>"
                                   maxlength="100" required>
                        </div>

                        
                        <span class="media_label">Tags</span>  
                        <div class="tags_container">                    
                              <?php foreach($tags_all as $tag): ?>
                              <div class="tag">
                                <input type="checkbox" name="tags[]" value="<?php echo $tag['TagID'] ?>"
                                	   id="tag_<?php echo $tag['TagID'] ?>" <?php echo isset($tags_medium[$tag['TagID']]) ? 'checked' : ''; ?>
                               	>
                               	<label for="tag_<?php echo $tag['TagID']?>" class="tag_label"><?php echo $tag['TagName']?></label>
                              </div>
                              <?php endforeach; ?> 	                              
                            
                            <?php if (empty($tags_all)): ?>
                                <p>Keine Tags vorhanden.</p>
                            <?php endif; ?>
                        </div>

                        <div class="media_info_row">
                            <span class="media_label">Medienart</span>
                            <span><?php echo htmlspecialchars($medium['Medienart']); ?></span>
                        </div>

                        <div class="media_info_row">
                            <span class="media_label">Datentyp</span>
                            <span><?php echo htmlspecialchars($medium['Datentyp']); ?></span>
                        </div>

                        <div class="media_info_row">
                            <span class="media_label">Größe</span>
                            <span><?php echo htmlspecialchars($medium['Groesse']); ?></span>
                        </div>
                        
                        <div class="media_info_row">
                            <span class="media_label">Upload von</span>
                            <span><?php echo htmlspecialchars($medium['Benutzername']); ?></span>
                        </div>

                        <div class="media_options">
                            <button type="submit" name="speichern" class="button">Speichern</button>
                            <a href="media_details.php?id=<?php echo $id; ?>" class="button" >Abbrechen</a>
                        </div>

                    </form>

                <?php else: ?>              

                    <!-- Medienansicht -->
                    <div class="media_info_row">
                        <span class="media_label">Name</span>
                        <span><?php echo htmlspecialchars($medium['Titel']); ?></span>
                    </div>

                    <div class="media_info_row tag_container">
                        <span class="media_label">Tags</span>
                        <span><?php echo !empty($tags_medium) ? htmlspecialchars(implode(', ', $tags_medium)) : 'Keine Tags'; ?></span>
                    </div>

                    <div class="media_info_row">
                        <span class="media_label">Medienart</span>
                        <span><?php echo htmlspecialchars($medium['Medienart']); ?></span>
                    </div>

                    <div class="media_info_row">
                        <span class="media_label">Datentyp</span>
                        <span><?php echo htmlspecialchars($medium['Datentyp']); ?></span>
                    </div>

                    <div class="media_info_row">
                        <span class="media_label">Größe</span>
                        <span><?php echo htmlspecialchars($medium['Groesse']); ?></span>
                    </div>
                    
                    <div class="media_info_row">
                            <span class="media_label">Upload von</span>
                            <span><?php echo htmlspecialchars($medium['Benutzername']); ?></span>
                    </div>
                    

            </div>

                    <div class="media_delete_button_unten">
                        <?php if (isset($_SESSION['NutzerID']) && $is_owner): ?>
                            <form action="media_details.php?id=<?php echo $id; ?>" method="post"
                                onsubmit="return confirm('Medium wirklich löschen?')">
                                <button type="submit" name="loeschen" class="options_button">
                                <img class="options" src="../../public/icons/trash-can.svg" alt="Löschen">
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

            

        </div>
    </section>

    <footer>
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>
</html>
<?php $connection->close(); ?>

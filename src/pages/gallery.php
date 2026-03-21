<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/db.php";

/* Dashboard Weiterleitung bei fehlender id */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = (int) $_GET['id'];
$erfolg = "";

/* Medium aus der Datenbank laden */
$sql = "SELECT MediumID, Titel, Medienart, Datentyp, Groesse, Path, NutzerID
        FROM Medium WHERE MediumID = $id";
$result = mysqli_query($connection, $sql);
$medium = $result->fetch_assoc();

/* Medium exisstiert nicht */
if (!$medium) {
    header("Location: dashboard.php");
    exit();
}

/* Besitzer oder Admin */
$is_owner = $_SESSION['NutzerID'] == $medium['NutzerID']
         || $_SESSION['Rolle'] === 'Admin';

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
    header("Location: dashboard.php");
    exit();
}

/* Neuer Name speichern */
if (isset($_POST['speichern']) && $is_owner) {
    $titel = addslashes($_POST['titel']);
    mysqli_query($connection, "UPDATE Medium SET Titel = '$titel' WHERE MediumID = $id");
    $medium['Titel'] = $_POST['titel'];
    $erfolg = "Änderungen gespeichert.";
}

/* Tags laden vorläufig */
$tags = [];
$tag_result = mysqli_query($connection,
    "SELECT t.TagName FROM Tag t
     JOIN Medium_has_Tag mht ON t.TagID = mht.TagID
     WHERE mht.MediumID = $id"
);
while ($row = $tag_result->fetch_assoc()) {
    $tags[] = $row['TagName'];
}

/* Bearbeitung */
$modus = (isset($_POST['bearbeiten']) || isset($_GET['bearbeiten'])) ? 'edit' : 'view';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mediendatenbank</title>
    <link rel="stylesheet" type="text/css" href="../../public/css/style.css">
</head>
<body>
    <header>
        <?php include __DIR__ . '/../../src/includes/header.php'; ?>
    </header>
    <?php include __DIR__ . '/../includes/background.php'; ?>

    <section class="gallery_section">
        <div class="media_details">
            <a href="dashboard.php" class="close_button">
                <img src="../../public/icons/schliessen.svg" alt="Zurück">
            </a>

            <!-- HTML-Element abhängig von Medienart -->
			<?php
			$medienart = $medium['Medienart'];
			$path      = htmlspecialchars($medium['Path']);

			if ($medienart === 'Bild') {
    		echo "<img class='media' src='$path' alt='Vorschau'>";

			} elseif ($medienart === 'Video') {
    		echo "
        		<video class='media' controls>
            		<source src='$path' type='video/mp4'>
        		</video>
    		";

			} elseif ($medienart === 'Hoerbuch') {
    		echo "
        		<div class='media media_audio'>
            		<audio controls>
                		<source src='$path' type='audio/mpeg'>
            		</audio>
        		</div>
    		";

			} elseif ($medienart === 'eBook') {
    		echo "
        		<iframe class='media' src='$path' title='eBook-Vorschau'>
            		<p>Vorschau nicht möglich. 
               			<a href='$path' target='_blank'>PDF herunterladen</a>
            		</p>
        		</iframe>
    		";
			}
			?>

            <div class="media_info">

                <?php if ($erfolg): ?>
                    <p class="media_erfolg"><?php echo $erfolg; ?></p>
                <?php endif; ?>

                <?php if ($modus === 'edit' && $is_owner): ?>

                    <!-- Bearbeitungsformular -->
                    <form action="gallery.php?id=<?php echo $id; ?>" method="post">

                        <div class="media_info_row">
                            <span class="media_label">Titel</span>
                            <input type="text" name="titel"
                                   value="<?php echo htmlspecialchars($medium['Titel']); ?>"
                                   maxlength="100" required>
                        </div>

                        <div class="media_info_row">
                            <span class="media_label">Tags</span>
                            <span><?php echo !empty($tags) ? htmlspecialchars(implode(', ', $tags)) : 'Keine Tags'; ?></span>
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

                        <div class="media_options">
                            <button type="submit" name="speichern">Speichern</button>
                            <a href="gallery.php?id=<?php echo $id; ?>">Abbrechen</a>
                        </div>

                    </form>

                <?php else: ?>

                           

                    <!-- Bearbeiten, fehlen noch Tags -->
                    <div class="media_info_row">
                        <span class="media_label">Name</span>
                        <span><?php echo htmlspecialchars($medium['Titel']); ?></span>
                        <?php if ($is_owner): ?>
                            <a href="gallery.php?id=<?php echo $id; ?>&bearbeiten=1" class="edit_inline_button">
                                <img src="../../public/icons/einstellungen.svg" alt="Bearbeiten">
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="media_info_row">
                        <span class="media_label">Tags</span>
                        <span><?php echo !empty($tags) ? htmlspecialchars(implode(', ', $tags)) : 'Keine Tags'; ?></span>
                        <?php if ($is_owner): ?>
                            <a href="gallery.php?id=<?php echo $id; ?>&bearbeiten=1" class="edit_inline_button">
                                <img src="../../public/icons/einstellungen.svg" alt="Bearbeiten">
                            </a>
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

            </div>

                    <div class="media_delete_button_unten">
                        <?php if ($is_owner): ?>
                            <form action="gallery.php?id=<?php echo $id; ?>" method="post"
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

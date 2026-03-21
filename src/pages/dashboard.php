<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/db.php";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../../public/css/style.css">
</head>
<body>
    <header>
		<?php include  __DIR__ . '/../includes/header.php'; ?>
	</header>

	<?php include __DIR__ . '/../includes/background.php'; ?>
    
    <section>
      <h1>Dashboard</h1>
      <p>Willkommen, <?php echo $_SESSION["Benutzername"]; ?>!</p>
        <div class="search_container">
            <form class="search_form" action="dashboard.php" method="post">
                <input class="search_bar" type="text" name="searchbar" value="<?php echo htmlspecialchars($_POST['searchbar'] ?? ''); ?>" placeholder="Suchberiffe..">
                
                <div class="search_options">
                    <select name="media_type" onchange="this.form.submit()">
                        <option value="">Alle Medien</option>
                        <option value="picture" <?php if(($_POST['media_type'] ?? '') === 'picture') echo 'selected'; ?>>Bilder</option>
                        <option value="audiobook" <?php if(($_POST['media_type'] ?? '') === 'audiobook') echo 'selected'; ?>>Hörbücher</option>
                        <option value="ebook" <?php if(($_POST['media_type'] ?? '') === 'ebook') echo 'selected'; ?>>E-Books</option>
                        <option value="video" <?php if(($_POST['media_type'] ?? '') === 'video') echo 'selected'; ?>>Videos</option>
                    </select>

                    <label>
                        <input type="checkbox" name="own_media" value="1" onchange="this.form.submit()" <?php if(isset($_POST['own_media'])) echo 'checked'; ?>> Nur eigene Medien
                    </label>
                </div>
            </form>

            

            <?php
            // verhindert 'undefined' Fehler bei erstaufruf der Seite
            if(!isset($_POST['searchbar'])) $_POST['searchbar'] = '';
            // Abfrage der Medien in der DB

            $search_terms = array_map('trim', explode(',', $_POST['searchbar']));

            $where_parts = [];

            foreach ($search_terms as $t) {
                $t = addslashes($t);
                $where_parts[] = "(
                t.TagName LIKE '%$t%' 
                OR m.Titel LIKE '%$t%' 
                OR m.Medienart LIKE '%$t%' 
                OR m.Datentyp LIKE '%$t%')
                ";
            }

            $and_parts = [];

            if (isset($_POST['own_media'])) {
                $user_id = $_SESSION['NutzerID'];
                $and_parts[] = "m.NutzerID = $user_id";
            }

            $media_type = $_POST['media_type'] ?? '';

            if ($media_type === 'picture') {
                $and_parts[] = "m.Medienart = 'Bild'";
            }

            if ($media_type === 'audiobook') {
                $and_parts[] = "m.Medienart = 'Hoerbuch'";
            }

            if ($media_type === 'ebook') {
                $and_parts[] = "m.Medienart = 'eBook'";
            }

            if ($media_type === 'video') {
                $and_parts[] = "m.Medienart = 'Video'";
            }

            $where = "(" . implode(" OR ", $where_parts) . ")";

            if (!empty($and_parts)) {
                $where .= " AND " . implode(" AND ", $and_parts);
            }

            $sql = "
            SELECT DISTINCT m.MediumID, m.Titel, m.Datentyp, m.Path FROM Medium m
            LEFT JOIN Medium_has_Tag mht ON m.MediumID = mht.MediumID
            LEFT JOIN Tag t ON t.TagID = mht.TagID
            WHERE $where
            ";
            
            $result = mysqli_query($connection,$sql);

            printf("<p>%s Datensätze gefunden</p>", $result->num_rows);
            ?>
        </div>

        <div class="media_frame">

            <?php
            // Ausgabe der Individuellen Datensaetze
            while ($entry = $result->fetch_assoc()) {
                printf("
                    <div class='media_container' onclick=\"window.location='gallery.php?id=%d'\">
                        <img class='media_preview' src='%s' alt='Error'>
                        <div class='media_description'>
                            <h4>%s</h4>
                            <p>%s</p>
                        </div>
                    </div>
                    ",
                    $entry["MediumID"],
                    $entry["Path"],
                    $entry["Titel"],
                    $entry["Datentyp"]
                );
            }
            ?>

        </div>
    </section>
    
    <footer>
        <?php include  __DIR__ . '/../includes/footer.php'; ?>
    </footer>

</body>
</html>

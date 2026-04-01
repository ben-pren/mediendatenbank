<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <div class="dashboard_heading">
        <h2>Willkommen in der Galerie<?php if(isset($_SESSION["Benutzername"])) echo ", " . $_SESSION["Benutzername"];?>!</h2>
    </div>
    <div class="search_container">
        <form class="search_form" action="dashboard.php" method="post">
            <!-- Suchleiste -->
            <input class="search_bar" type="text" name="searchbar" value="<?php echo htmlspecialchars($_POST['searchbar'] ?? ''); ?>" placeholder="Nach Titel oder Tags suchen (mit Komma trennen)">

            <div class="search_options">
                <!-- Dropdown zur Auswahl von Medienkategorien -->
                <select class="dropdown" name="media_type" onchange="this.form.submit()">
                    <option value="">Alle Kategorien</option>
                    <option value="picture" <?php if(($_POST['media_type'] ?? '') === 'picture') echo 'selected'; ?>>Bilder</option>
                    <option value="audiobook" <?php if(($_POST['media_type'] ?? '') === 'audiobook') echo 'selected'; ?>>Hörbücher</option>
                    <option value="ebook" <?php if(($_POST['media_type'] ?? '') === 'ebook') echo 'selected'; ?>>E-Books</option>
                    <option value="video" <?php if(($_POST['media_type'] ?? '') === 'video') echo 'selected'; ?>>Videos</option>
                </select>

                <!-- Checkbox um nur innerhalb der eigenen Medien zu suchen -->
                <?php  if(isset($_SESSION["NutzerID"])) { ?>
                <label class="checkbox">
                    <input type="checkbox" name="own_media" onchange="this.form.submit()" <?php if(isset($_POST['own_media'])) echo 'checked'; ?>> Nur eigene Medien
                </label>
                <?php  }?>
            </div>

            <!-- Icon um Suche zu leeren -->
            <?php if (($_POST['searchbar'] ?? '') !== '' || ($_POST['media_type'] ?? '') !== '' || !empty($_POST['own_media'])) { ?>
                      <img class="reset" src="/MedienDB/public/icons/reset.svg" alt="Reset" onclick="window.location.href = window.location.pathname">
            
            <?php } ?>
        </form>

        <?php        
        // verhindert 'undefined' Fehler bei erstaufruf der Seite
        if(!isset($_POST['searchbar'])) $_POST['searchbar'] = '';

        // Suchbegriffe (Suchleiste) in einem Array speichern (Trennung durch Kommata)
        $search_terms = array_map('trim', explode(',', $_POST['searchbar']));

        // Array's fuer die Suchfilter
        $search_params = [];
        $filters = [];
        $having_params = [];
        
        // Aus den Suchbegriffen SQL-Klauseln generieren
        foreach ($search_terms as $term) {
            $term = addslashes($term);
            $clause = "(m2.Titel LIKE '%$term%' OR t2.TagName LIKE '%$term%')";
            $search_params[] = $clause;
            $having_params[] = "SUM(CASE WHEN $clause THEN 1 ELSE 0 END) > 0";
        }

        // Aus dem Dropdown-Menue und der Checkbox SQL-Klauseln generieren
        $media_type = $_POST['media_type'] ?? '';

        if ($media_type === 'picture') {
            $filters[] = "m.Medienart = 'Bild'";
        }

        if ($media_type === 'audiobook') {
            $filters[] = "m.Medienart = 'Hoerbuch'";
        }

        if ($media_type === 'ebook') {
            $filters[] = "m.Medienart = 'eBook'";
        }

        if ($media_type === 'video') {
            $filters[] = "m.Medienart = 'Video'";
        }

        if (isset($_POST['own_media'])) {
            $user_id = $_SESSION['NutzerID'];
            $filters[] = "m.NutzerID = $user_id";
        }

        // SQL-Klauseln zusammenfuehren
        $where_clause = "(" . implode(" OR ", $search_params) . ")";

        if (!empty($filters)) {
            $where_clause .= " AND " . implode(" AND ", $filters);
        }

        $having_clause = "(" . implode(" AND ", $having_params) . ")";

        // Vollstaendige SQL-Anfrage zusammensetzen
        $sql = "
        SELECT m.MediumID, m.Titel, m.Medienart, m.Datentyp, m.Path, m.NutzerID, n.Benutzername, t.TagName FROM Medium m
        LEFT JOIN Nutzer n ON m.NutzerID = n.NutzerID
        LEFT JOIN Medium_has_Tag mht ON m.MediumID = mht.MediumID
        LEFT JOIN Tag t ON t.TagID = mht.TagID
        WHERE m.MediumID IN (
            SELECT m2.MediumID
            FROM Medium m2
            LEFT JOIN Medium_has_Tag mht2 ON m2.MediumID = mht2.MediumID
            LEFT JOIN Tag t2 ON t2.TagID = mht2.TagID
            WHERE $where_clause
            GROUP BY m2.MediumID
            HAVING
            $having_clause
            )
        ORDER BY m.MediumID, t.TagName;
        ";

        $result = mysqli_query($connection,$sql);

        $entries = [];
        $unique_entries = [];

        // Anzahl der einmaligen MediumID's bestimmen
        while ($entry = $result->fetch_assoc()) {
            $entries[] = $entry;
            $unique_entries[$entry['MediumID']] = true;
        }

        $number_of_entries = count($unique_entries);

        if ($number_of_entries === 1) {
            printf("<p>1 Datensatz gefunden</p>");
        } else {
            printf("<p>%s Datensätze gefunden</p>", $number_of_entries);
        }
        ?>
    </div>

    <div class="media_frame">
        <?php
        $lastMediumID = null;

        // Ausgabe der Individuellen Datensaetze
        // Da eine MediumID mehrfach auftaucht, wenn das Medium mehrere Tag's hat muss jeder Datensatz dahingehend geprueft werden
        // Gehoeren mehrere aufeinanderfolgende Medien zur gleichen ID werden die allgemeinen Info's nur einmal ausgegeben und
        // anschliessend nur noch die Tag's
        foreach ($entries as $entry) {

            // Pruefen ob der Datensatz noch zum aktuellen Medium gehoert
            if ($entry['MediumID'] !== $lastMediumID) {

                // Schliessen des aktuellen media_container's vor dem Wechsel zum Naechsten Datensatz
                if ($lastMediumID !== null) {
                    printf("</div>"); // tag_container
                    printf("</div>"); // media_description
                    printf("</div>"); // media_container
                }

                // Verlinkung fuer die Detailansicht beim Anklicken von einem Medium
                printf("<div class='media_container' onclick=\"window.location='gallery.php?id=%d'\">", $entry["MediumID"]);

                // Auswahl des korrekten Containers fuer das Vorschaubild (Platzhalter fuer eBook's und Video's)
                if ($entry['Medienart'] === 'Bild') {
                    printf("<img class='media_preview' src='%s' alt='Error'>", $entry["Path"]);
                }

                if ($entry['Medienart'] === 'Hoerbuch') {
                    printf("<img class='media_preview' src='../../public/icons/hoerbuch.svg' alt='Error'>");
                }

                if ($entry['Medienart'] === 'eBook') {
                    printf("<img class='media_preview' src='../../public/icons/ebook.svg' alt='Error'>");
                }

                if ($entry['Medienart'] === 'Video') {
                    printf("<video class='media_preview' src='%s' preload='auto'></video>", $entry["Path"]);
                }

                // Allgemeine Info's zum Medium ausgeben
                printf("<div class='media_description'>");
                printf("<div class='media_info_titel'>
                          <h4>Titel</h4>
                          <p>%s (%s)</p>
                        </div>",$entry["Titel"], $entry["Datentyp"]);
                printf("<div class='media_info_user'>
                          <h4>Upload von</h4>  
                          <p>%s</p>
                        </div>"
                       ,$entry['Benutzername']);
                printf("<h4 class='taglabel'>Tags</h4>");
                printf("<div class='tag_container'>");

                $lastMediumID = $entry['MediumID'];
            }

            // Tag's ausgeben
            if (!empty($entry['TagName'])) {
                printf("<p class='tags'>%s</p>", $entry['TagName']);
            } else {
                printf("<p class='no_tags'>Keine</p>");
            }
            
        }

        // Schliessen des letzten media_container's, wenn vorhanden
        if ($lastMediumID !== null) { 
            printf("</div>"); // tag_container
            printf("</div>"); // media_description
            printf("</div>"); // media_container
        }
        ?>
    </div>
</section>

<footer>
    <?php include  __DIR__ . '/../includes/footer.php'; ?>
</footer>
</body>
</html>

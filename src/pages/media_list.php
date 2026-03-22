<?php 
require_once '../includes/auth.php';
require_once __DIR__ . "/../config/db.php";
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
		<?php include  __DIR__ . '/../includes/header.php'; ?>
	</header>
		<?php include __DIR__ . '/../includes/background.php'; ?>
	<section>
		<form action="media_list.php" method="post">
			<input name="searchbar" value="<?php echo htmlspecialchars($_POST['searchbar'] ?? ''); ?>">
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

		$where = implode(" OR ", $where_parts);

		$sql = "
			SELECT DISTINCT m.Titel, m.Datentyp, m.Path FROM Medium m
			LEFT JOIN Medium_has_Tag mht ON m.MediumID = mht.MediumID
			LEFT JOIN Tag t ON t.TagID = mht.TagID
			WHERE $where
		";
		
		$result = mysqli_query($connection,$sql);

		printf("<p>%s Datensätze gefunden", $result->num_rows);
		printf("<ul class='media_list'>");

        // Ausgabe der Individuellen Datensaetze
		while ($entry = $result->fetch_assoc()) {
			printf("<li>
				<a href='#'>
				<img class='media_preview' src='%s' alt='Error'>
				<p>%s</p>
				</a>			
				</li>"
				, $entry["Path"], $entry["Titel"]);
		}

		printf("</ul>");
		?>
	</section>

	<footer>
		<?php include  __DIR__ . '/../includes/footer.php'; ?>
	</footer>
</body>
</html>
<?php $connection->close();?>

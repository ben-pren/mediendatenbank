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
	
	<section>
		<?php
		require_once __DIR__ . "/../config/db.php";

		// Abfrage der Medien in der DB
		$sql = "SELECT Titel FROM Medium";
		$result = mysqli_query($connection,$sql);

		printf("<p>%s Datensätze gefunden", $result->num_rows);
		printf("<ul class='media_list'>");

        // Ausgabe der Individuellen Datensaetze
		while ($entry = $result->fetch_assoc()) {
			printf("<li>
						<a href='#'>
							<img class='media_preview' src='../../public/icons/benutzer.svg' alt='Error'>
							<p>%s</p>
						</a>			
					</li>"
					, $entry["Titel"]);
		}

		printf("</ul>");
		?>
	</section>

	<footer>
		<?php include  __DIR__ . '/../includes/footer.php'; ?>
	</footer>
</body>
</html>
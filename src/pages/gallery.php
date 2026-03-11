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
		<?php include  __DIR__ . '/../../src/includes/header.php'; ?>
	</header>

	<section>
		<div class="media_details">
		
			<img class="media" src="../../public/icons/benutzer.svg" alt="Error">

			<table>
				<tr><td><h4>Bildtitel</h4></td></tr>
				<tr><td><p>Schlagworte zum Bild</p></td></tr>
				<tr><td><p>Bildbeschreibung</p></td></tr>
			</table>

			<div class="media_options">
				<a href="#"><img class="options" src="../../public/icons/plus.svg" alt="Error"></a>
				<a href="#"><img class="options" src="../../public/icons/einstellungen.svg" alt="Error"></a>
				<a href="#"><img class="options" src="../../public/icons/schliessen.svg" alt="Error"></a>
			</div>
		</div>
	</section>

	<footer>
		<?php include  __DIR__ . '/../src/includes/footer.php'; ?>
	</footer>
</body>
</html>
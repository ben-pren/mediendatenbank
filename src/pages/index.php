<?php session_start();?>
<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Mediendatenbank</title>
	<link rel="stylesheet" type="text/css" href="../../public/css/style.css">
	<link rel="stylesheet" type="text/css" href="../../public/css/index.css">

</head>
<body>
	<header>
		<?php include  __DIR__ . '/../includes/header.php'; ?>
	</header>

	<?php include __DIR__ . '/../includes/background.php'; ?>
	
	<section>
		<h1>Willkommen im MedienHub!</h1>
		<!-- <h2>H2</h2> -->
		<h3></h3>
		<h3>Hier kannst du Medien hochladen, ansehen und herunterladen.</h3>
		<p>Schnell, einfach und kostenlos.</p>

		<div class="foto-container">
        	<img src="../../public/icons/bild1_landingpage.jpg" class="foto-stil" alt="Vorschau 1: Buch">
        	<!--  <img src="icons/bild2_landingpage.jpg" class="foto-stil" alt="Vorschau 2: New York"> -->
			<video src="../../public/icons/video1_landingpage.mp4" class="foto-stil" autoplay loop muted></video>
        	<img src="../../public/icons/bild3_landingpage.jpg" class="foto-stil" alt="Vorschau 3: Dackel">
		</div>

		<p>Wir unterstützen: Fotos, Videos, Hörbücher &amp; eBooks!</p> <br>
		<p>Schau in der Galerie vorbei, um zu sehen, was andere Nutzer bereits hochgeladen haben, <br>
		   und melde dich an, um selbst etwas hochzuladen</p>
	</section>

	<footer>
		<?php include  __DIR__ . '/..//includes/footer.php'; ?>
	</footer>
</body>
</html>
<?php
require_once __DIR__ . "/../config/db.php";
session_start();

/* Für die Fehlermeldung */
$fehler = "";

$erfolg = "";
if (isset($_SESSION["erfolg"])) {
	$erfolg = $_SESSION["erfolg"];
	unset($_SESSION["erfolg"]);
}

/* Prüfen, ob Login Formular per POST abgeschickt wurde */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$login = trim($_POST["nutzer"]);
	$passwort = trim($_POST["passwort"]);


	/*Felder auf Inhalt prüfen*/     
	if (empty($login) || empty($passwort)) {
		$fehler = "Bitte einen gültigen Benutzer und Passwort eingeben.";
	} else {
		/*Datenbankabgleich auf Email oder Nutzer und Weiterleitung auf Dashboard*/
		$sql = "SELECT * FROM nutzer WHERE Benutzername = '$login' OR Email = '$login'";
		$result = mysqli_query($connection,$sql);

		if (mysqli_num_rows($result) > 0){
			$nutzer = mysqli_fetch_assoc($result);

			if ($passwort == $nutzer["Passwort"]) {
				$_SESSION["NutzerID"] = $nutzer["NutzerID"];
				$_SESSION["Benutzername"] = $nutzer["Benutzername"];
				$_SESSION["Rolle"] = $nutzer["Rolle"];

				header("Location: dashboard.php");
				exit();
			} else { 
				$fehler = "Das eingegebene Passwort ist falsch.";
			}
		} else {
			$fehler = "Benutzername oder E-Mail nicht gefunden.";
		}
	}
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login - Mediendatenbank</title>
	<link rel="stylesheet" type="text/css" href="../../public/css/style.css">
</head>
<body>
	<header>
		<?php include __DIR__ . '/../includes/header.php'; ?>
	</header>

	<section class="login_container">
		<div class="login_box">
			<h1>Anmelden</h1>
		
		<?php 
		if (!empty($erfolg)) {
			echo "<p>$erfolg</p>";
		}
		?>

		<?php
		if (!empty($fehler)) {
			echo $fehler;
		}
		?>

		<form method="post" action="">
			<div>
				<label for="nutzer">Benutzername oder E-Mail:</label>
				<input type="text" name="nutzer" id="nutzer">
			</div>

			<div>
				<label for="passwort">Passwort:</label>
				<input type="password" name="passwort" id="passwort">
			</div>

			<div>
				<button type="submit">Login</button>
			</div>
		</form>
	</section>

	<footer>
		<?php include  __DIR__ . '/../includes/footer.php'; ?>
	</footer>
</body>
</html>

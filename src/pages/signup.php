<?php require_once __DIR__ . "/../config/db.php";
session_start();

$fehler = "";
$erfolg = "";

/* Rolle ist immer User */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$benutzername = trim($_POST["benutzername"]);
    $email = trim($_POST["email"]);
	$passwort = trim($_POST["passwort"]);
	$passwort_wiederholen = trim($_POST["passwort_wiederholen"]);

    if (empty($benutzername) || empty($email) || empty($passwort) || empty($passwort_wiederholen)) {
        $fehler = "Bitte alle Felder ausfüllen";
		/*Prüfen ob Passwörter übereinstimmen*/
	} elseif ($passwort != $passwort_wiederholen){
		$fehler = "Die Passwörter stimmen nicht überein.";
	} else {
		/*Prüfen ob Benutzer schon vorhanden ist*/
        $sql_check = "SELECT * FROM nutzer WHERE Benutzername = '$benutzername' OR Email = '$email'";
        $result = mysqli_query($connection, $sql_check);

        if (mysqli_num_rows($result) > 0) {
            $fehler = "Benutzername oder E-Mail bereits vorhanden!";
        } else {
			/*Neuen Benutzer anlegen*/
            $sql = "INSERT INTO nutzer (Email, Benutzername, Passwort, Rolle)
            VALUES ('$email', '$benutzername', '$passwort', 'user')";

			/*Bei Erfolg Weiterleitung auf loginpage */
            if (mysqli_query($connection, $sql)) {
				$_SESSION["erfolg"] = "Registrierung erfolgreich. Du kannst dich jetzt anmelden.";
                header("Location: /MedienDB/src/pages/login.php");
				exit();
            } else {
                $fehler = "Fehler beim Speichern, Bitte versuche es noch einmal!";
            }
        }
    }

}
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Registrieren - Mediendatenbank</title>
	<!-- Ich hatte hier Probleme mit dem cache, falls das bei euch nicht so ist, einfach ?v=2 löschen -->
	<link rel="stylesheet" type="text/css" href="../../public/css/style.css?v=2">
</head>
<body>
	<header>
		<?php include __DIR__ . '/../includes/header.php'; ?>
	</header>
		<?php include __DIR__ . '/../includes/background.php'; ?>
	<section class="signup_container">
		<div class="signup_box">
			<h1>Registrierung</h1>

			<?php
			if (!empty($fehler)) {
				echo "<p>$fehler</p>";
			}

			if (!empty($erfolg)) {
				echo "<p>$erfolg</p>";
			}
			?>

			<form method="post" action="">
				<div>
					<label for="benutzername">Benutzername:</label>
					<input type="text" name="benutzername" id="benutzername" maxlength="45">
				</div>

				<div>
					<label for="email">E-Mail:</label>
					<input type="email" name="email" id="email" maxlength="100">
				</div>

				<div>
					<label for="passwort">Passwort:</label>
					<input type="password" name="passwort" id="passwort" maxlength="100">
				</div>

				<div>
					<label for="passwort_wiederholen">Passwort erneut eingeben:</label>
					<input type="password" name="passwort_wiederholen" id="passwort_wiederholen" maxlength="100">
				</div>

				<div>
					<button type="submit">Registrieren</button>
				</div>
			</form>
		</div>
	</section>

	<footer>
		<?php include __DIR__ . '/../includes/footer.php'; ?>
	</footer>
</body>
</html>

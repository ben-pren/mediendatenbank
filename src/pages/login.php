<?php
require_once __DIR__ . "/../config/db.php";
session_start();

/* Für die Fehlermeldung */
$fehler = "";

/* Prüfen, ob Login Formular per POST abgeschickt wurde */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $benutzername = trim($_POST["benutzername"]);
    $passwort = trim($_POST["passwort"]);


/*Felder auf Inhalt prüfen*/     
    if (empty($benutzername) || empty($passwort)) {
        $fehler = "Bitte einen gültigen Benutzername und Passwort eingeben.";
    } else {
/*Datenbankabgleich und Weiterleitung auf Dashboard*/
        $sql = "SELECT * FROM nutzer WHERE Benutzername = '$benutzername'";
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
            $fehler = "Benutzername nicht gefunden.";
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

	<section>
		<h1>Anmelden</h1>

        <?php if (!empty($fehler)) {
            echo $fehler;
        }
        ?>

        <form method="post" action="">
			<div>
				<label for="benutzername">Benutzername:</label>
				<input type="text" name="benutzername" id="benutzername">
			</div>

			<div>
				<label for="passwort">Passwort:</label>
				<input type="password" name="passwort" id="passwort">
			</div>

			<div>
				<button type="submit">Anmelden</button>
			</div>
		</form>
	</section>

	<footer>
		<p>Gruppenarbeit WEB42</p>
	</footer>

</body>
</html>

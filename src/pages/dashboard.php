<?php
require_once __DIR__ . "/../includes/auth.php";
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

    <section>
        <h1>Dashboard</h1>
        <p>Willkommen, <?php echo $_SESSION["Benutzername"]; ?>!</p>
    </section>

    <footer>
        <?php include  __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>
</html>
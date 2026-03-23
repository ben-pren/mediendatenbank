<?php 
// falls Session noch nicht gestartet wurde (Sicherheitscheck)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Abmelden
if (isset($_POST['abmelden'])) {
    if (isset($_SESSION['temp_uploads'])) {
        foreach ($_SESSION['temp_uploads'] as $datei) {
            // Pfadbereinigung für das Löschen temporärer Dateien
            $media_path = substr($datei['temp_path'], 2);
            $delete_path = $_SERVER['DOCUMENT_ROOT'] . "/MedienDB/src" . $media_path;
            if (file_exists($delete_path)) {
                unlink($delete_path);
            }
        }
    }
    session_destroy();
    // Nach dem Abmelden zurück zur Startseite
    header("Location: /MedienDB/public/index.php");
    exit();
}

$showlogin = isset($_SESSION['NutzerID']);
?>

<nav class="dropdown_menu" id="menu">
    <button type="button" class="menu_button" onclick="toggle_menu()">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <ul class="dropdown_content" id="dropdown_elements">
        <li><a href="/MedienDB/public/index.php">Startseite</a></li>
        
        <?php if($showlogin): ?>
            <li><a href="/MedienDB/src/pages/dashboard.php">Galerie</a></li>

            <li><a href="/MedienDB/src/pages/media_upload.php">Medienupload</a></li>
            
            <li><a href="/MedienDB/src/pages/tag_request_form.php">Tag anfragen</a></li>

            <?php if(isset($_SESSION['Rolle']) && $_SESSION['Rolle'] === 'Admin'): ?>
                <li class="menu_admin"><a href="/MedienDB/src/pages/tags_admin.php">Tag-Anfragen</a></li>
                <li class="menu_admin"><a href='/MedienDB/src/pages/tags.php'>Tags</a></li>
            <?php endif; ?>
        <?php endif; ?> 
        
        <li><a href="/MedienDB/src/pages/impressum.php">Impressum</a></li>
    </ul>
</nav>

<div class="header_container logo">
    <a href="/MedienDB/public/index.php">
        <img src="/MedienDB/public/icons/LogoMedienDB.svg" alt="Logo MedienHub" class="img_logo">
    </a>
    <h1>MedienHub</h1>
</div>

<?php if(!$showlogin): ?>
    <div class="header_container signup_buttons">
	  <a href="/MedienDB/src/pages/login.php" class="login_button">Anmelden</a>
      <a href="/MedienDB/src/pages/signup.php" class="login_button">Registrieren</a>     
    </div>
<?php endif; ?>

<?php if($showlogin): ?>
    <div class="header_container header_user_container">
        <img src="/MedienDB/public/icons/benutzer.svg" class="header_user" alt="User Icon">
        <form action="/MedienDB/public/index.php" method="post" style="display: inline;">
            <p class="header_user">
                <strong><?php echo htmlspecialchars($_SESSION['Benutzername']); ?></strong><br>
                <button type="submit" name="abmelden" class="logout_button">Abmelden</button>  
            </p>
        </form>
    </div>
<?php endif; ?>

<script type="text/javascript">
    // Drop-down ausklappen
    function toggle_menu() {
        document.getElementById("dropdown_elements").classList.toggle("show");
    }

    // Drop-down (sub) ausklappen
    function toggle_submenu() {
        document.getElementById("dropdown_subelements").classList.toggle("show");
    }

</script>

<!-- Burger-Menue zum Navigieren -->
<?php 

if (isset($_POST['abmelden'])) {
    if (isset($_SESSION['temp_uploads'])) {
        foreach ($_SESSION['temp_uploads'] as $datei) {
            $media_path = substr($datei['temp_path'], 2);
            $delte_path = $_SERVER['DOCUMENT_ROOT'] . "/MedienDB/src" . $media_path;
            unlink($delte_path);
        }
    }
    
    session_destroy();
    header("Refresh: 0");
}

$showlogin = isset($_SESSION['NutzerID']);
?>

  <nav class="dropdown_menu" id="menu">
    <!-- Button zum ausklappen von Menü -->
    <button type="button" class="menu_button" onclick="toggle_menu()">
      <span></span>
      <span></span>
      <span></span>
	</button>
	<!-- Menüinhalt -->	
	<ul class="dropdown_content" id="dropdown_elements">
	  <li><a href="/MedienDB/public/index.php">Startseite</a></li>
	  <li><a href="/MedienDB/src/pages/dashboard.php">Dashboard</a></li>
	  <li>
        <a href="#" onclick="toggle_submenu()">Meine Medien ></a>
	      <ul class="dropdown_content" id="dropdown_subelements">
            <li><a href="/MedienDB/src/pages/media_list.php">Alle</a></li>
	    	<li><a href="#">Bilder</a></li>
	     	<li><a href="#">Videos</a></li>
            <li><a href="#">Hörbücher</a></li>
	      </ul>
      </li>
	  <li><a href="/MedienDB/src/pages/media_upload.php">Neue Medien</a></li>
	  <?php if(isset($_SESSION["Rolle"])){
	          if($_SESSION["Rolle"] === "Admin" ) {
	            echo "<li><a href='/MedienDB/src/pages/tags.php'>Tags</a></li>";
	          }
	        }
	   ?>
	  <li><a href="#">Impressum</a></li>
    </ul>
    
  </nav>
  
    <div class="header_container logo">
      <img src="/MedienDB/public/icons/LogoMedienDB.svg" alt="Logo MedienHub" class="img_logo"> 
      <h1>MedienHub</h1>
    </div>
    
    <!-- Anzeigen von Login Buttons wenn nicht angemeldet -->
    <?php if(!$showlogin) {?>
    <div class="header_container signup_buttons" >
	  <a href="/MedienDB/src/pages/login.php" class="login_button">Anmelden</a>
      <a href="/MedienDB/src/pages/signup.php" class="login_button">Registrieren</a>		
	</div>
	<?php }?>
	
	<!-- Anzeigen von Benutzername und abmelden wenn angemeldet -->
	<?php if($showlogin) {?>
	<div class="header_container header_user_container"  >
	  <img src="/MedienDB/public/icons/benutzer.svg" class="header_user" alt="User Icon">
	  <form action="/MedienDB/public/index.php" method="post">
	    <p class="header_user">
	      <?php echo $_SESSION['Benutzername']?> <br>
	      <button type="submit" name="abmelden" class="logout_button">abmelden</button>  
	    </p>
	  </form>
	</div>
	<?php }?>

<script type="text/javascript">
	// Burger-Menue ausklappen
	function toggle_menu() {
		document.getElementById("dropdown_elements").classList.toggle("show");
	}

	// Burger-Submenue ausklappen
	function toggle_submenu() {
		document.getElementById("dropdown_subelements").classList.toggle("show");
	}
</script>

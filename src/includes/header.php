<!-- Burger-Menue zum Navigieren -->
<header>
  <nav class="dropdown_menu" id="menu">
    <!-- Button zum ausklappen von Menü -->
    <button type="button" class="menu_button" onclick="toggle_menu()">
      <span></span>
      <span></span>
      <span></span>
	</button>
	<!-- Menüinhalt -->	
	<ul class="dropdown_content" id="dropdown_elements">
	  <li><a href="/public/index.php">Startseite</a></li>
	  <li>
        <a href="#" onclick="toggle_submenu()">Meine Medien ></a>
	      <ul class="dropdown_content" id="dropdown_subelements">
            <li><a href="#">Alle</a></li>
	    	<li><a href="#">Bilder</a></li>
	     	<li><a href="#">Videos</a></li>
            <li><a href="#">Hörbücher</a></li>
	      </ul>
      </li>
	  <li><a href="#">Neue Medien</a></li>
	  <li><a href="#">Impressum</a></li>
    </ul>
    
  </nav>
  
    <div class="header_container logo" > 
      <h1>Mediendatenbank</h1>
    </div>
    
    <div class="header_container signup_buttons" >
	  <a href="#" class="login_button">Anmelden</a>
      <a href="#" class="login_button">Registrieren</a>		
	</div>
	
</header>

<link rel="stylesheet" type="text/css" href="/public/css/style.css">

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

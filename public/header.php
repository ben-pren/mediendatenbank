<header>
	<!-- Burger-Menue zum Navigieren -->
	<nav class="dropdown_menu" id="menu">
		<button type="button" class="menu_button" onclick="toggle_menu()">
			<span></span>
			<span></span>
			<span></span>
		</button>
		
		<ul class="dropdown_content" id="dropdown_elements">
			<li><a href="index.php">Startseite</a></li>
			<li>
				<a href="#" onclick="toggle_submenu()">Meine Medien ></a>
				<ul class="dropdown_content" id="dropdown_subelements">
					<li><a href="#">Alle</a></li></li>
					<li><a href="#p">Bilder</a></li></li>
					<li><a href="#p">Videos</a></li></li>
					<li><a href="#p">Hoerbuecher</a></li></li>
				</ul>
			</li>
			<li><a href="#">Neue Medien</a></li>
			<li><a href="#">Impressum</a></li>
		</ul>
	</nav>

	<h1>Home</h1>

	<h1>Mediendatenbank</h1>

	<a href="#" class="login_button">
		<p>Login</p>
	</a>
</header>

<link rel="stylesheet" type="text/css" href="css/style.css">

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
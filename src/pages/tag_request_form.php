<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pfad zur Datenbank
require_once '../includes/auth.php';
require_once '../config/db.php'; 


// abbruch ändern von Tag
if(isset($_POST['neu_tag_abbruch'])) {
    header("Location: " . "/MedienDB/src/pages/tag_request_form.php");
    exit;
}
// Anfrage Tag ändern
if(isset($_POST['neu_tag']) && !empty($_POST['altered_tag_request'])) {
    echo "mom";
    $nutzerID = $_SESSION['NutzerID'];
    $neu_tag_name = $_POST['altered_tag_request'];
    $tag_id = $_POST['neu_tag'];
    $kommentar = $_POST['Kommentar'] ?? null;
    
    $stmt = $connection->prepare(
        "INSERT INTO Tag_Request (NutzerID, RequestedTagName, TagID, Kommentar, Status) VALUES (?, ?, ?, ?, 'offen')"
        );
    
    $stmt->bind_param("isis", $nutzerID, $neu_tag_name, $tag_id, $kommentar);
    
    if ($stmt->execute()) {
        // Redirect zurück zur Startseite (src/pages/index.php)
        echo "<script>alert('Vielen Dank! Deine Anfrage wurde gesendet.'); window.location.href='../../src/pages/index.php';</script>";
    } else {
        echo "Fehler beim Senden: " . $connection->error;
    }
}

$nutzerID = $_SESSION['NutzerID'];
// abfrage
$requests = $connection->query("SELECT tr.*, n.Benutzername, t.TagName
                                FROM Tag_Request tr
                                JOIN Nutzer n ON tr.NutzerID = n.NutzerID
                                LEFT JOIN Tag t ON tr.TagID = t.TagID
                                WHERE tr.NutzerID = $nutzerID");

$alleRequests = [];
while ($row = $requests->fetch_assoc()) {
    $alleRequests[] = $row;
}


$neue_Tags = array_filter($alleRequests, function($row) {
    return is_null($row['TagID']);
});
    
$tags_Aendern = array_filter($alleRequests, function($row) {
        return !is_null($row['TagID']);
});


?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Tag anfragen</title>
    <link rel="stylesheet" type="text/css" href="../../public/css/style.css">
    <link rel="stylesheet" type="text/css" href="../../public/css/tag_request_form.css">
</head>
<body>
  <header>
    <?php include '../includes/header.php'; ?>
  </header>
    <?php include __DIR__ . '/../includes/background.php'; ?>
	
    <div class="content_container">
      <h2>Neuen Tag anfragen</h2>
      <p>Du vermisst ein Schlagwort? Schick uns einen Vorschlag!</p>
        
      <form action="tag_request_db.php" method="POST" class="form_add">
            
        <label for="RequestedTagName">Gewünschter Tag:</label>
        <input type="text" name="RequestedTagName" id="RequestedTagName" placeholder="z.B. Weltall" required style="width: 300px; padding: 10px; margin-top: 5px;">
            
        <label for="Kommentar">Kommentar (optional):</label>
        <textarea name="Kommentar" id="Kommentar" rows="6" maxlength="500"></textarea>
            
        <button type="submit">Anfrage senden</button>
      </form>
      
      
      
      <h2>Tagänderung anfragen</h2>
      <p>Du hast einen Grund um ein Tag zu verändern? <br>
         Klicke einfach das <img src = "/MedienDB/public/icons/pen.svg">Symbol und gib deine Vorschlag ein.
      </p>
<?php      
      // anzeigen von form zur änderug von tag wenn auf Stift geklickt wird
     if (isset($_POST['aendern'])) {
         $tag_id = $_POST['aendern']; ?>
         <div class="container_tag_change">
           <?php $sql = "SELECT * FROM tag WHERE TagID = $tag_id";
                 $result = mysqli_query($connection,$sql);
                 $result = mysqli_fetch_assoc($result);
           if($result){?>
           <form action="" method="post" class="form_add">
             <label for="altered_tag_request">Altes Tag: <?php echo $result['TagName'];?> </label>
             <input type="text" id="altered_tag_request" name="altered_tag_request" maxlength="50" placeholder="Neuer Name" >
             
             <label for="Kommentar">Kommentar (optional):</label>
             <textarea name="Kommentar" id="Kommentar" rows="6" maxlength="500"></textarea>
             
           	 <button type="submit" name="neu_tag" value="<?php echo $tag_id;?>">Anfrage Senden</button>
           	 <button type="submit" name="neu_tag_abbruch">Abbruch</button>
           </form>
           </div>
<?php 
      }
}?> 
      
      <!-- Tagausgabe -->
	  <div class="container_taglist">
        <?php 
        $sql = "SELECT * FROM tag ORDER BY LOWER(TagName) ASC";
        $result = mysqli_query($connection,$sql);
          if($result) {
             while ($row = mysqli_fetch_assoc($result)) {  ?>
          	   <div class="container_tag">
                 <div><?php echo  $row['TagName'];?></div>
                 <form action="" method="post" class="container_button">
                   <button type="submit" name="aendern" value="<?php echo $row['TagID']?>"><img src = "/MedienDB/public/icons/pen.svg"></button>
	             </form>
	           </div>
        <?php
             }?>
     <?php
          }?>
      </div>
    </div>
    
    
    <!-- Anzeige von Anfragestatus -->
	<section>   
	  <h2>Tag-Anfragen</h2>
	  <h3>Tag Anfragen Neue Tags</h3>
	  <div class="background">
        <div class="contaiener_request_head">
          <div>Gewünschter Tag</div>
          <div class="kommentar">Kommentar</div>
          <div class="kommentar">Admin Kommentar</div>
          <div>Erstellungszeitpunkt</div>
          <div>Status</div>
        </div>
         
          <?php if (count($neue_Tags) > 0): ?>
            <?php foreach ($neue_Tags as $row): ?>
              <div class="container_request_body">
                <div><?php echo htmlspecialchars($row['RequestedTagName']); ?></div>
                <div class="kommentar"><?php echo htmlspecialchars($row['Kommentar']); ?></div>
                <div class="kommentar"><?php echo $row['Kommentar_Admin']; ?></div>
                <div><?php echo htmlspecialchars($row['ErstelltAm']); ?></div>
                <div><?php echo htmlspecialchars($row['Status']); ?></div>
			  </div>
            <?php endforeach; ?> 	
          <?php else: ?>
      		<p class="center">Keine Tag-Anfragen abgesendet.</p>  
          <?php endif;?>
        </div>  
    </section>       
            
    <section>   
      <h3>Tag Anfragen Ändern von Tags</h3>      
      <div class="background">
        <div class="contaiener_request_head">
          <div>Alter Tag</div>
          <div>Gewünschter Tag</div>
          <div class="kommentar">Kommentar</div>
          <div class="kommentar">Admin Kommentar</div>
          <div>Erstellungszeitpunkt</div>
          <div>Status</div>
        </div>    

          <?php if (count($tags_Aendern) > 0): ?>
            <?php foreach ($tags_Aendern as $row): ?>
              <div class="container_request_body">
                <div><?php echo htmlspecialchars($row['TagName']);?></div>
                <div><?php echo htmlspecialchars($row['RequestedTagName']); ?></div>
                <div class="kommentar"><?php echo htmlspecialchars($row['Kommentar']); ?></div>
                <div class="kommentar"><?php echo $row['Kommentar_Admin']; ?></div>
                <div><?php echo htmlspecialchars($row['ErstelltAm']); ?></div>
                <div><?php echo htmlspecialchars($row['Status']); ?></div>
			  </div>
            <?php endforeach; ?>
          <?php else: ?>
           <p class="center">Keine Tag-Anfragen abgesendet.</p>
          <?php endif; ?>
      </div>  
    </section> 
    
    
  <footer>
    <?php include  __DIR__ . '/../includes/footer.php'; ?>
  </footer>	  
</body>
</html>
<?php 
$connection->close();
?>

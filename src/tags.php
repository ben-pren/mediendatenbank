<?php
use function pcov\waiting;

require_once '../includes/auth.php';
require_once __DIR__ . "/../config/db.php";


//checken für doppelte tags und einfügen von tags in DB
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['tags'])  && isset($_POST['tags'])) {

    $tag_titel = $_POST['tags'];
    $tag_titel_compare = strtolower(preg_replace('/\s+/', '', $tag_titel));
    
    $sql = "SELECT * FROM tag";
    $result = mysqli_query($connection,$sql);
    $doppeltag  = false;
    //check doppel tags
    if($result) {
       while ($row = mysqli_fetch_assoc($result)) {
          $comparedb = strtolower(preg_replace('/\s+/', '', $row['TagName']));
          if($comparedb === $tag_titel_compare) {
             $doppeltag = true; 
             $_SESSION['upload_erfolg'] = "Bereits Tag mit gleichem Namen vorhanden";
             break;
          }
       }
    }
    //einfügen in db falls keine doppelten tags vorhanden
    if (!$doppeltag){
        $stmt = $connection->prepare("INSERT INTO tag (TagName) VALUES (?)");
        $stmt->bind_Param("s",$tag_titel);
        
        if($stmt->execute()) {
            $_SESSION['upload_erfolg'] = "Upload erfolgreich";
        } else {
            $_SESSION['upload_erfolg'] = "Fehler beim Upload";
        }
        $stmt->close(); 
    }   
    header("Location: " . "/MedienDB/src/pages/tags.php");
    exit;
}

?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tags</title>
    <link rel="stylesheet" type="text/css" href="../../public/css/style.css">
    <link rel="stylesheet" type="text/css" href="../../public/css/tags.css">
</head>

<body>
  <header>
    <?php include  __DIR__ . '/../includes/header.php'; ?>
  </header>
	<?php include __DIR__ . '/../includes/background.php'; ?>
  <main>
  <div>
    <form action="" method="post">
      <label for=tags>Tag hinzufügen:</label>
      <input type="text" id="tags" name="tags" maxlength="50" required>
      <button type="submit" name="hochladen">Tag hochladen</button>
    </form>
    <p>
      <?php
      if (isset($_SESSION['upload_erfolg'])) {
          echo $_SESSION['upload_erfolg'];
          unset($_SESSION['upload_erfolg']);
      }
      ?>
    </p>
  </div>
  
  <div>
    <?php 
      $sql = "SELECT * FROM tag";
      $result = mysqli_query($connection,$sql);
      if($result) {
          while ($row = mysqli_fetch_assoc($result)) {  ?>
             <div>
               <?php echo  $row['TagName'];?>
               <form action="" method="post">
                 <button type="submit" name="aendern"><img src = "/MedienDB/public/icons/einstellungen.svg"></button>
	             <button type="submit" name="loeschen"><img src = "/MedienDB/public/icons/schliessen.svg"></button>
	           </form>
             </div>
     <?php
          }?>
 <?php
      }?>
  </div>  
  </main>
  
  <footer>
    <?php include  __DIR__ . '/../includes/footer.php'; ?>
  </footer>	
  
</body>
</html>
<?php 
$connection->close();
?>
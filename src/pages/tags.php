<?php
use function pcov\waiting;

require_once '../includes/auth.php';
require_once __DIR__ . "/../config/db.php";

if ($_SESSION['Rolle'] !== 'Admin') {
    die("Zugriff verweigert.");
}

// abbruch ändern von Tag
if(isset($_POST['neu_tag_abbruch'])) {
   header("Location: " . "/MedienDB/src/pages/tags.php");
   exit;
}

// Fehlermeldung wenn kein neuer Name eingeben 
if(isset($_POST['neu_tag']) && empty($_POST['altered_tag'])) {
    $_SESSION['aendern_erfolg'] = "Bitte neuen Namen eingeben";
}
    
    
// Ändern von Tag
if(isset($_POST['neu_tag']) && !empty($_POST['altered_tag'])) {
   $neu_tag_name = $_POST['altered_tag'];
   $tag_titel_compare = strtolower(preg_replace('/\s+/', '', $neu_tag_name));
   $tag_id = $_POST['neu_tag'];
   
   $sql = "SELECT * FROM tag WHERE TagID != '$tag_id'";
   $result = mysqli_query($connection,$sql);
   $doppeltag  = false;
   //check doppel tags
   if($result) {
       while ($row = mysqli_fetch_assoc($result)) {
           $comparedb = strtolower(preg_replace('/\s+/', '', $row['TagName']));
           if($comparedb === $tag_titel_compare) {
               $doppeltag = true;
               $_SESSION['aendern_erfolg'] = "Bereits Tag mit gleichem Namen vorhanden";
               break;
           }
       }
   }
   // hier wird Tag geändert falls der neue Name nicht vergeben ist
   if(!$doppeltag) {
   $stmt = $connection->prepare("UPDATE tag SET TagName= ? WHERE TagID = ?");
   $stmt->bind_Param("si", $neu_tag_name, $tag_id);
   
   if($stmt->execute()) {
       $_SESSION['aendern_erfolg'] = "Tag erfolgreich verändert.";
   } else {
       $_SESSION['aendern_erfolg'] = "Fehler beim Ändern des Tags.";
   }
   $stmt->close();
   
   header("Location: " . "/MedienDB/src/pages/tags.php");
   exit;
   }
}


// löschen eines Tags aus DB
if (isset($_POST['loeschen'])) {
    $tag_id = $_POST['loeschen'];
    
    $stmt = $connection->prepare("DELETE FROM tag WHERE TagID = ?");
    $stmt->bind_Param("i", $tag_id);
    
    if($stmt->execute()) {
       $_SESSION['upload_erfolg'] = "Tag erfolgreich entfernt.";
    } else {
       $_SESSION['upload_erfolg'] = "Fehler beim Entfernen des Tags.";
    }
    $stmt->close();
    
    header("Location: " . "/MedienDB/src/pages/tags.php");
    exit;
}

//checken für doppelte tags und einfügen von tags in DB
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['tags'])) {

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
  <div class="container_add">
    <h2>Tag hinzufügen</h2>
    <form action="" method="post">
      <input type="text" id="tags" name="tags" maxlength="50" required>
      <button type="submit" name="hochladen">Tag hochladen</button>
    </form>
    <p>
      <?php
      if (isset($_SESSION['upload_erfolg'])) {
          echo $_SESSION['upload_erfolg'];
          unset($_SESSION['upload_erfolg']);
      }

      if (isset($_SESSION['delete_erfolg'])) {
          echo $_SESSION['delete_erfolg'];
          unset($_SESSION['delete_erfolg']);
      }
      
      if (isset($_SESSION['aendern_erfolg'])) {
          echo $_SESSION['aendern_erfolg'];
          unset($_SESSION['aendern_erfolg']);
      }
      ?>
    </p>
  </div>
<?php
// anzeigen von form zur änderug von tag wenn auf Stift geklickt wird
if (isset($_POST['aendern'])) {
    $tag_id = $_POST['aendern']; ?>
    <div class="container_tag_change">
      <?php $sql = "SELECT * FROM tag WHERE TagID = $tag_id";
            $result = mysqli_query($connection,$sql);
            $result = mysqli_fetch_assoc($result);
      if($result){?>
      <h3><?php echo "Tag Name: " . $result['TagName'];?></h3>
      <form action="" method="post">
        <input type="text" id="altered_tag" name="altered_tag" maxlength="50" placeholder="Neuer Name" >
      	<button type="submit" name="neu_tag" value="<?php echo $tag_id;?>">Tag ändern</button>
      	<button type="submit" name="neu_tag_abbruch">Abbruch</button>
      </form>
      </div>
<?php 
      }
}?> 
  
<!-- Alphabetische Ausgabe von Tags mit Buttons zum ändern/löschen -->  
  <div class="container_taglist">
    <?php 
      $sql = "SELECT * FROM tag ORDER BY LOWER(TagName) ASC";
      $result = mysqli_query($connection,$sql);
      if($result) {
          while ($row = mysqli_fetch_assoc($result)) {  ?>
          	   <div class="container_tag">
                 <div><?php echo  $row['TagName'];?></div>
                 <form action="" method="post" class="container_buttons">
                   <button type="submit" name="aendern" value="<?php echo $row['TagID']?>"><img src = "/MedienDB/public/icons/pen.svg"></button>
	               <button type="submit" name="loeschen" value="<?php echo $row['TagID']?>"><img src = "/MedienDB/public/icons/trash-can.svg"></button>
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

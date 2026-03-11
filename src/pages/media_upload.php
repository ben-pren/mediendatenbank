<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
//Funktionen
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

function typeToMedia($datatype) {
    $medientyp = "";
    if($datatype === "png" || $datatype ==="jpeg" || $datatype ==="jpg"){
        $medientyp = "Bild";
    }
    elseif($datatype === "mp4"){
        $medientyp = "Video";
    }
    elseif($datatype === "mp3"){
        $medientyp = "Hoerbuch";
    }
    elseif($datatype === "html" || $datatype ==="pdf"){
     $medientyp = "eBook";
 }
 return $medientyp;
}

// Abbrechen von Upload und löschen von Sessiondaten sowie temporären Uploads
if (isset($_POST['abbrechen']) && isset($_SESSION['temp_uploads'])) {
    foreach ($_SESSION['temp_uploads'] as $datei) {
        $media_path = substr($datei['temp_path'], 2);
        $delte_path = $_SERVER['DOCUMENT_ROOT'] . "/MedienDB/src" . $media_path;
        unlink($delte_path);
    }
    unset($_SESSION['temp_uploads']);
}


// permanentes speichern in DB
$upload_ziel = $_SERVER['DOCUMENT_ROOT'] . "/MedienDB/src" . "/UserUploads/";

if (!file_exists($upload_ziel)) {
    mkdir($upload_ziel, 0777, true);
}

if (isset($_POST['hochladen']) && isset($_SESSION['temp_uploads'])) {
    
    foreach ($_SESSION['temp_uploads'] as $datei_index => $datei) {
        $final_path = $upload_ziel . uniqid() . "_" . $datei["voll_name"];
        
        if (rename($datei['temp_path'], $final_path)) {
            $titel_final = $_POST["titel_" . $datei_index];
            $medienart = $datei['Medienart'];
            $datentyp = $datei['Datentyp'];
            $groesse = $datei['Groesse'];
            $userid = $_SESSION['NutzerID'];
            
            $stmt = $connection->prepare("INSERT INTO medium (Titel, Medienart, Datentyp, Groesse, Path, NutzerID)
              VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_Param("sssssi", $titel_final, $medienart, $datentyp, $groesse, $final_path, $userid);
            
            if($stmt->execute()) {
                echo "Datei " . $datei_index + 1 . "<br>";
            }
            $stmt->close();
        }
    }
    unset($_SESSION['temp_uploads']);
    
    
}



//Temporäres Speichern der Datein um vor Abbruch zu schützen und Titel zu vergeben
if(isset($_FILES['userfiles']) && !isset($_SESSION['temp_uploads'])){ 
 $files = $_FILES['userfiles'];
 $filecount = count($files['name']);
 
 $_SESSION['temp_uploads'] = [];
 
 $upload_zielTEMP = "../UserUploadsTEMP/";
 
 if (!file_exists($upload_zielTEMP)) {
     mkdir($upload_zielTEMP, 0777, true);
 }
 
 for ($i = 0; $i < $filecount; $i++){
     if ($files['error'][$i] === UPLOAD_ERR_OK) {
         
         $erlaubte_typ = ['png', 'jpg', 'jpeg', 'mp3', 'mp4', 'html', 'pdf'];
         $extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
         
         if (!in_array($extension, $erlaubte_typ)) {
             echo("Eine Datei ist vom typ " . $extension . ".<br>");
             die("Nur PNG, JPG, JPEG, MP3, MP4, HTMl und PDF Datein sind erlaubt.");
         }
         
         $temp_path = $upload_zielTEMP . uniqid() . "_" . $files["name"][$i];
         if (move_uploaded_file($files["tmp_name"][$i], $temp_path)) {
             $_SESSION['temp_uploads'][] = [
                 "temp_path" => $temp_path,
                 "temp_titel" => pathinfo($files['name'][$i], PATHINFO_FILENAME),
                 "Medienart" => typeToMedia($extension),
                 "Datentyp"  => $extension,
                 "Groesse"   => formatBytes($files["size"][$i]),
                 "voll_name" => $files['name'][$i]   
             ];   
         }
     } else {
         echo "<p>Uploadfehler bei Datei " . ($i+1) . "</p>";
     }
 }
}

$anzeige = isset($_SESSION['temp_uploads']) && !empty($_SESSION['temp_uploads']);
?>
<!-- HTMl anzeige abbhängig con $anzeige Variabel -->
<!DOCTYPE html>
<html lang="de">

<head>
  <title>Media Upload</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" href="../../public/css/style.css">
</head>

<body>
  <header>
    <?php include '../includes/header.php'; ?>
</header>

<main>
    <?php if(!$anzeige) {?>
      <h2>Datein Upload</h2>
      <p></p>
      <form action="media_upload.php" method="post" enctype="multipart/form-data">
        <label for="files">Medienupload</label>
        <input 
        type="file" 
        id="userfiles" 
        name="userfiles[]" 
        multiple 
        accept=".png, .jpg, .jpeg , .mp3, .mp4, .html"
        required
        >
        <button type="submit">Upload vorbereiten</button>
    </form>
<?php }?>

<?php if($anzeige) {?>
  <h2>Anpassung von Titel und Tags</h2>
  
  <form action="media_upload.php" method="post">
    <?php foreach($_SESSION['temp_uploads'] as $datei_index => $datei) {?>
      <h3>Datei <?php echo $datei_index + 1?>: <?php echo $datei['voll_name']?></h3>
      <p>
          Originaler Titel: <?php echo $datei['temp_titel']?> <br>
          Medienart       : <?php echo $datei['Medienart']?> <br>
          Datentyp        : <?php echo $datei['Datentyp']?> <br>
          Größe           : <?php echo $datei['Groesse']?> <br>
      </p>      
      
      <label for=titel_<?php echo$datei_index?>>Neuer Titel:</label>
      <input
      type="text"
      id="titel_<?php echo$datei_index?>"
      name="titel_<?php echo$datei_index?>"
      value="<?php echo$datei['temp_titel']?>" 
      required
      >  <br>
  <?php }?>
  <button type="submit" name="hochladen">Dateien hochladen</button>
  <button type="submit" name="abbrechen">Upload abbrechen</button>
</form>
<?php }?>

</main>

<footer>
    <?php include  __DIR__ . '/../includes/footer.php'; ?>
</footer>
</body>
</html>



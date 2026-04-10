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

function mediaToImg($medientyp) {
    $icon_path = "";
    if($medientyp === "Bild"){
        $icon_path = "../../public/icons/bild.svg";
    }
    if($medientyp === "Video"){
        $icon_path = "../../public/icons/video.svg";
    }
    if($medientyp === "Hoerbuch"){
        $icon_path = "../../public/icons/hoerbuch.svg";
    }
    if($medientyp === "eBook"){
        $icon_path = "../../public/icons/ebook.svg";
    }
    return $icon_path;    
}

// Gibt alle Tags in Db in array form aus
function getTags($connection) {
    $all_tags = [];
    $sql = "SELECT * FROM tag ORDER BY LOWER(TagName) ASC";
    $result = mysqli_query($connection,$sql);
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $all_tags[] = $row;
        }
    }
    return $all_tags;
}
$all_tags = getTags($connection);

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
$upload_ziel = $_SERVER['DOCUMENT_ROOT'] . "/MedienDB/src/UserUploads/";
$id = "";
$upload_erfolg = "";
if (!file_exists($upload_ziel)) {
    mkdir($upload_ziel, 0777, true);
}

if (isset($_POST['hochladen']) && isset($_SESSION['temp_uploads'])) {
    
    foreach ($_SESSION['temp_uploads'] as $datei_index => $datei) {
        $id = uniqid();
        $final_path = $upload_ziel . $id . "_" . $datei["voll_name"];
        $final_pathdb = "http://localhost/MedienDB/src/UserUploads/" . $id . "_" . $datei["voll_name"];
        
        if (rename($datei['temp_path'], $final_path)) {
            $titel_final = $_POST["titel_" . $datei_index];
            $medienart = $datei['Medienart'];
            $datentyp = $datei['Datentyp'];
            $groesse = $datei['Groesse'];
            $userid = $_SESSION['NutzerID'];
            
            $stmt = $connection->prepare("INSERT INTO medium (Titel, Medienart, Datentyp, Groesse, Path, NutzerID)
                                          VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_Param("sssssi", $titel_final, $medienart, $datentyp, $groesse, $final_pathdb, $userid);
            
            if($stmt->execute()) {
                $medium_id = mysqli_stmt_insert_id($stmt);   
                $selected_tags = "tags_for_" . $datei_index;
                
                if(isset($_POST[$selected_tags])) {
                   $stmt_tags = $connection->prepare("INSERT INTO Medium_has_Tag (MediumID, TagID) VALUES (?, ?)"); 
                   
                   foreach ($_POST[$selected_tags] as $tag_ID) {
                       $stmt_tags->bind_Param("ii", $medium_id, $tag_ID);
                       $stmt_tags->execute();
                   }
                   $stmt_tags->close();
                }  
                $upload_erfolg = "Hochladen Erfolgreich";
            } else {
                $upload_erfolg = "Fehler beim Hochladen";
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
                   "voll_name" => $files['name'][$i],
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
    <title>Medien Upload</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../../public/css/style.css">
    <link rel="stylesheet" type="text/css" href="../../public/css/upload.css">
</head>

<body>
    <header>
        <?php include '../includes/header.php'; ?>
    </header>
  	
    <?php include __DIR__ . '/../includes/background.php'; ?>
    <main>
        <?php if(!$anzeige) {?>
        <div class="container_beschreibung">
            <h2>Medien Upload</h2>
            <p>
               Hier kannst du Medien auf unsere Website hochladen. Klicke dafür einfach auf den Button 'Dateien auswählen' 
               und wähle mit 'Strg' + 'Linksklick' aus, welche Dateien du hochladen willst. <br>
               Nach der Auswahl der Dateien kannst du durch das Klicken auf 'Upload vorbereiten'
               jedem deiner Uploads einen Titel und passende Tags hinzufügen. <br>
               Hochladen kannst du hier Bilder, Videos, Hörbücher und eBooks. Folgende Dateiformate sind hier für den Upload zugelassen.
            </p> 
        </div>
      
        <div class="container_dateiformate">      
            <div>
                <img src="../../public/icons/bild.svg" class="medienimg">
                <p class="font_white">Bilder: <br> png/jpg/jpeg </p>
            </div>
        
            <div>
                <img src="../../public/icons/video.svg" class="medienimg">
                <p class="font_white">Videos: <br> mp4</p>
            </div>
            
            <div>
                <img src="../../public/icons/hoerbuch.svg" class="medienimg">
                <p class="font_white">Horbücher: <br> mp3</p>
            </div>
            
            <div>
                <img src="../../public/icons/ebook.svg" class="medienimg">
                <p class="font_white">eBooks: <br> html/pdf </p>
            </div>        
        </div>
      
        <div class="container_beschreibung">
            <p>
              Falls manche deiner Dateien nicht in den erlaubten Formaten vorliegen, kannst du diese auch online in die gewünschten Formate umwandeln.<br>
              Zum Beispiel gibt es viele Websites, auf denen du kostenlos EPUB-Dateien (Standardformat für eBooks) in HTML- oder PDF-Dateien
              umwandeln lassen kannst.
            </p>
        </div>
      
      
        <form action="media_upload.php" method="post" enctype="multipart/form-data" class="form_upload">
            <label for="userfiles"> Dateien auswählen</label>
            <input 
              type="file" 
              id="userfiles" 
              name="userfiles[]"
              multiple 
              accept=".png, .jpg, .jpeg , .mp3, .mp4, .html, .pdf"
              required
            >
            <button type="submit">Upload vorbereiten</button>
        </form>
        <p class= "upload_succes"><?php printf($upload_erfolg); ?></p>
        <?php }?>
        
        <?php if($anzeige) {?>
        <h2>Anpassung von Titel und Tags</h2>
      
        <form action="media_upload.php" method="post" class="form_edit">
            <div class="container_medien">
                <?php foreach($_SESSION['temp_uploads'] as $datei_index => $datei) {?>
                <div>
                    <img src=<?php echo mediaToImg($datei['Medienart']);?> class="media_icon">
                    <h3>Datei <?php echo $datei_index + 1?></h3>
                    <p>Originaler Titel<br> <?php echo $datei['temp_titel']?></p> <br>
                    <div class="container_mediendetails">
                        <div>Medienart       <br> <?php echo $datei['Medienart']?></div> <br>
                        <div>Datentyp        <br> <?php echo $datei['Datentyp']?></div> <br>
                        <div>Größe           <br> <?php echo $datei['Groesse']?></div> <br>
                    </div>
              
                    <label for=titel_<?php echo$datei_index?> class="titel_label">Neuer Titel:</label>
                    <input
                      type="text"
                      id="titel_<?php echo$datei_index?>"
                      name="titel_<?php echo$datei_index?>"
                      value="<?php echo$datei['temp_titel']?>" 
                      maxlength="100"
                      required
                    >  <br>
              
                    <h3>Tagauswahl</h3>
                    <div class="tags_container">
                        <?php foreach ($all_tags as $tag) {?>
                        <div class="tag">      
                            <input 
                            type="checkbox"
                            name="tags_for_<?php echo $datei_index?>[]"
                            id="<?php echo $tag['TagID']?>_<?php echo $datei_index?>"
                            value="<?php echo $tag['TagID']?>"
                            >
                            <label for="<?php echo $tag['TagID']?>_<?php echo $datei_index?>" class="tag_label"><?php echo $tag['TagName']?></label>
                        </div>
                        <?php }?>
                    </div>
              
                </div>     
                <?php }?> 
            </div> 
            <div class="container_buttons">
                <button type="submit" name="hochladen">Dateien hochladen</button>
                <button type="submit" name="abbrechen">Upload abbrechen</button>
            </div>  
        </form>
        <?php }?>
    </main>
  
    <footer>
        <?php include '../includes/footer.php'; ?>
    </footer>
</body>
</html>
<?php $connection->close();?>
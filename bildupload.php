 <!DOCTYPE html>
<html>
 <head>
  <title>Bilder hochladen</title>
 </head>
<body>

<fieldset>
 <legend>Bild hochladen</legend>

<?php

// Verzeichnis in das die Bilder hoch geladen
// werden sollen (ausgehend von dieser Datei).
// Das Verzeichnis benötigt Schreibrechte!
$verzeichnis = "bilder/";

// Die Größe des Bildes das maximal
// hoch geladen werden darf (in Bytes).
$maxgroesse = 1024; // 1024 Bytes = 1 KB (1048576 Bytes = 1 MB)

// Maximale Abmessungen (Breite/Höhe)
$max_breite = 800; // Pixel
$max_hoehe = 500; // Pixel

// Angabe der Mimetypen die hoch geladen werden dürfen.
$mimetypen = array(
 "png" => "image/png",
 "jpg" => "image/jpeg",
 "jpg" => "image/pjpeg",
 "jpeg" => "image/jpeg",
 "gif" => "image/gif",
);

// Maximale Länge des Dateinamens
$maxlaenge = 35;

// Eine bereits vorhandene Datei mit gleichen Namen ersetzen (ja/nein)
// Bei "nein" wird die Datei mit dem Namen und einer Zufallszahl, umbenannt.
$ersetzen = "nein";

// Passwortschutz (ja/nein)
$passwortschutz = "nein";

// Passwort
$passwort = "user";

echo '<form action="' . $_SERVER["SCRIPT_NAME"] . '" method="post" enctype="multipart/form-data">
 <p><label>Bild auswählen: <input type="file" name="datei" size="25"></label> <br>
 Dateiformat: ' . implode(", ", array_unique(array_keys($mimetypen))) . 
' - Dateigröße max.: ' . (number_format(($maxgroesse / 1024), 2, ",", ".")) . ' KB</p>' . 
($passwortschutz == "ja" ? '<p><label>Passwort: <input type="password" name="passwort" required="required"></label></p>' : '') . 
'<input type="hidden" name="MAX_FILE_SIZE" value="' . $maxgroesse . '">
 <input type="submit" name="submit" value="Bild hochladen">';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 if ($passwortschutz == "ja") {
  if ($_POST["passwort"] != $passwort) {
   die('<p>Sie haben keine Berechtigung!</p>');
  }
 }
 if (is_writeable($verzeichnis)) {
  if (isset($_FILES["datei"]["name"]) && $_FILES["datei"]["name"] != "") {
   if ($_FILES["datei"]["error"] === UPLOAD_ERR_OK) {
    if (is_uploaded_file($_FILES["datei"]["tmp_name"])) {
     list($tmp_breite, $tmp_hoehe) = getImageSize($_FILES["datei"]["tmp_name"]);
     if ($tmp_breite <= $max_breite) {
      if ($tmp_hoehe <= $max_hoehe) {
       if ($_FILES["datei"]["size"] <= $maxgroesse) {
        $array = explode(".", basename($_FILES["datei"]["name"]));
        $dateiendung = strtolower(end($array));
        if (in_array($dateiendung, array_keys($mimetypen)) && $tmp_breite > 0 && $tmp_hoehe > 0) {
         if (in_array($_FILES["datei"]["type"], $mimetypen)) {
          $dateiname = preg_replace("/[^a-z0-9_-]/", "", strtolower(strtr(strip_tags($array[0]), "äöüß", "aous")));
          if (strlen($dateiname) < 4) {
           $dateiname = mt_rand(1, 9999);
          }
          $neuername = substr($dateiname, 0, $maxlaenge) . "." . $dateiendung;
          if (file_exists($verzeichnis . $neuername)) {
           if ($ersetzen == "ja") {
            unlink($verzeichnis . $neuername);
           }
           else {
            $neuername = substr($dateiname, 0, ($maxlaenge - 5)) . "_" . mt_rand(1, 9999) . "." . $dateiendung;
           }
          }
          if (move_uploaded_file($_FILES["datei"]["tmp_name"], $verzeichnis . $neuername)) {
           echo '<p>Die Datei wurde erfolgreich hochgeladen<br> <img src="' . $verzeichnis . $neuername . '">';
           echo ' <br>' . $neuername . ' - ' . number_format(($_FILES["datei"]["size"] / 1024), 2, ",", ".") . ' KB</p>';
          }
          else {
           echo '<p>Beim hochladen der Datei &bdquo;' . $_FILES["datei"]["name"] . '&rdquo; ist leider ein Fehler aufgetreten!</p>';
          }
         }
         else {
          echo '<p>Ungültiger Mimetyp: "' . $_FILES["datei"]["type"] . '"!</p>';
         }
        }
        else {
         echo '<p>Das Dateiformat: "' . $dateiendung . '" ist nicht erlaubt!</p>';
        }
       }
       else {
        echo '<p>Die Datei &bdquo;' . $_FILES["datei"]["name"] . '&rdquo; ist mit ' . number_format(($_FILES["datei"]["size"] / 1024), 2, ",", ".") . ' KB leider zu groß!</p>';
       }
      }
      else {
       echo '<p>Die Höhe des Bildes ist leider zu groß, max: ' . $max_hoehe . ' Pixel!</p>';
      }
     }
     else {
      echo '<p>Die Breite des Bildes ist leider zu groß, max: ' . $max_breite . ' Pixel!</p>';
     }
    }
   }
  }
  else {
   echo '<p>Bitte wählen Sie eine Datei aus!</p>';
  }
 }
 else {
  echo '<p>Das Verzeichnis: "' . $verzeichnis . '" besitzt keine Schreibrechte!</p>';
 }
}
echo '</form>';
?>

</fieldset>

</body>
</html> 
<?
//==================================================================================
// Datei.......: einstuebern.php
// Beschreibung: Schreibt die gemachten Änderungen an den Einstellungen in die Datenbank
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 40 $
// zuletzt geändert : 	$Date: 2009-05-29 15:53:44 +0200 (Fr, 29 Mai 2009) $
// Author:			$Author: tobias $
//
// 23.10.2011 TGUSI74
// + SQLite3 Anpassungen
//==================================================================================

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

  <meta name="Paketname" content="SQLite DUG Tool">
  <meta name="Dateiname" content="doinstall.php">
  <meta name="Dateiversion" content="1.0">
  <meta name="Dateidatum" content="28.03.2009">
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <link rel="stylesheet" type="text/css" href="./mystyle.css">
</head>
<body>
<center><center><span style="font-weight: bold;">SQLite DUG Tool<br>
		Einstellungen &uuml;bernehmen</span><br>
</center>
<br>
<br>

<?
//Hilfsfunktion zum Pfad anlegen
function mkdir_rek($dir)
{
  if (!is_dir($dir))
  {
	mkdir_rek(dirname($dir));
    mkdir($dir,777);
  }
}

include ("sqlitebasis.php");

$db = new SQLite3($dbpfad);

//Den Pfad für die späteren Graphendiagramme anlegen
$neuerkurzpfad = stripslashes($_REQUEST["Graphenpfad"]);
//überprüfe, ab der Pfad mit einem Backslash endet, sonst füge einen hinzu
if (substr($neuerkurzpfad, -1, 1) != "\\") {$neuerkurzpfad = $neuerkurzpfad."\\";}


//Den Pfad für die späteren Graphendiagramme anlegen
$neuerpfad = stripslashes($_REQUEST["erwGraphenpfad"]);
//überprüfe, ab der Pfad mit einem Backslash endet, sonst füge einen hinzu
if (substr($neuerpfad, -1, 1) != "\\") {$neuerpfad = $neuerpfad."\\";}
//Verzeichnis neu anlegen, wenn es noch nicht existiert
if (!is_dir($neuerpfad))
{
		mkdir_rek($neuerpfad);
		if (is_dir($neuerpfad))
		{
			echo "Das Verzeichnis '".$neuerpfad."' existierte noch nicht und wurde neu angelegt.<br>";
		}
		else
		{
			echo "Das Verzeichnis '".$neuerpfad."' existierte noch nicht und konnte auch nicht neu angelegt werden.<br>";
		}
}

	$select = "UPDATE Zusatz SET Wert = '".$_REQUEST["DBupdateID"]."' WHERE Bezeichnung = 'updateScriptID';"."\r \n";
	$select = $select."UPDATE Zusatz SET Wert = '".$_REQUEST["GraphupdateID"]."' WHERE Bezeichnung = 'GraphenupdateID';"."\r \n";
	$select = $select."UPDATE Zusatz SET Wert = '".$_REQUEST["DUGKategorieID"]."' WHERE Bezeichnung = 'DUGKategorieID';"."\r \n";
	$select = $select."UPDATE Zusatz SET Wert = '".$_REQUEST["DUGMediaKategorieID"]."' WHERE Bezeichnung = 'DUGMediaKategorieID';"."\r \n";
	$select = $select."UPDATE Zusatz SET Wert = '".$neuerkurzpfad."' WHERE Bezeichnung = 'Graphenpfad';"."\r \n";
	$select = $select."UPDATE Zusatz SET Wert = '".$neuerpfad."' WHERE Bezeichnung = 'erwGraphenpfad';"."\r \n";
	$select = $select."UPDATE Zusatz SET Wert = '".$_REQUEST["GraphenKonfiguration"]."' WHERE Bezeichnung = 'GraphenKonfiguration';"."\r \n";
	$select = $select."UPDATE Zusatz SET Wert = '".($_REQUEST["registriereMedia"] ? 1 : 0)."' WHERE Bezeichnung = 'registriereMedia';"."\r \n";
	$select = $select."UPDATE Zusatz SET Wert = '".($_REQUEST["erlaubeY2Achse"] ? 1 : 0)."' WHERE Bezeichnung = 'erlaubeY2Achse';"."\r \n";

	$db->query($select);


$db->close();
?>
<br>
<br>
<center>
<span style="font-weight: bold;">Einstellungen in Datenbank gespeichert.</span><br>

<br>
<a href="http:./index.php">Zur Startseite</a>

</center>
</body>
</html>

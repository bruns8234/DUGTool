<?
//==================================================================================
// Datei.......: doinstall.php
// Beschreibung: Legt die Datenbank an und setzt die Umgebungsparameter
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 83 $
// zuletzt geändert : 	$Date: 2009-10-02 17:47:53 +0200 (Fr, 02 Okt 2009) $
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
  <meta name="Dateiversion" content="1.4">
  <meta name="Dateidatum" content="19.04.2009">
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <link rel="stylesheet" type="text/css" href="./mystyle.css">
</head>
<body>
<center><span style="font-weight: bold;">Installationsscript
zum SQLite Datenbank Und Graphen (DUG) Tool</span><br>
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



//Installationsroutine
$dbpfad = IPS_GetKernelDir().$_REQUEST["dbdateiname"];
if (file_exists($dbpfad)) {echo "Datenbank existiert bereits. <br>";}
$db = new SQLite3($dbpfad);
$sqliteerror = $db->lastErrorCode();
if ($sqliteerror == 0)
{
	echo $dbpfad. " erfolgreich neu angelegt.<br>";
	$select = " CREATE TABLE [Zusatz] (
				[Bezeichnung] TEXT  UNIQUE NOT NULL,
				[Wert] TEXT  NOT NULL
				);";
	$db->query($select);

	$select = "CREATE TABLE [Graph] (
				[ID] INTEGER  PRIMARY KEY NOT NULL,
				[Name] TEXT  NOT NULL,
				[Ueberschrift] TEXT  NULL,
				[ErstellungsIntervall] INTEGER  NOT NULL,
				[ErstellungsOffset] INTEGER  NOT NULL,
				[GroesseX] INTEGER DEFAULT '1' NOT NULL,
				[GroesseY] INTEGER DEFAULT '1' NOT NULL,
				[DatenIntervall] INTEGER DEFAULT '1' NOT NULL,
				[GraphenTyp] INTEGER DEFAULT '0' NOT NULL,
				[BarGraphIntervall] INTEGER DEFAULT '0' NOT NULL
				);";
	$db->query($select);

	$select = "	CREATE TABLE [Graphenliste] (
				[VarID] INTEGER  NOT NULL,
				[GraphID] INTEGER  NOT NULL,
				[VarFarbe] TEXT  NOT NULL,
				[DarGanzzahl] BOOLEAN DEFAULT '0' NOT NULL,
				[LinienTyp] INTEGER DEFAULT '1' NOT NULL,
				[ZWert] INTEGER DEFAULT '1' NOT NULL,
				[Filter] TEXT DEFAULT 'keinFilter' NOT NULL
				);";
	$db->query($select);

	$select = "	CREATE TABLE [Variable] (
				[ID] INTEGER  PRIMARY KEY NOT NULL,
				[IPSID] INTEGER  UNIQUE NOT NULL,
				[Name] TEXT  NULL,
				[Typ] INTEGER DEFAULT '1' NOT NULL,
				[Einheit] TEXT  NULL,
				[MaxIntervallZeit] INTEGER  NULL,
				[MaxAnzahl] INTEGER  NULL
				);";
	$db->query($select);

	$select = " CREATE TABLE [VarEreignis] (
				[ID] INTEGER  PRIMARY KEY NOT NULL,
				[DatumZeit] INTEGER  NOT NULL,
				[Wert] TEXT  NULL,
				[VarID] INTEGER  NOT NULL
				);";
	$db->query($select);


	$select = "	CREATE UNIQUE INDEX [IDX_GRAPHENLISTE_VARID] ON [Graphenliste](
				[GraphID]  ASC,
				[VarID]  ASC
				);

				CREATE INDEX [IDX_VAREREIGNIS_] ON [VarEreignis](
				[VarID]  ASC,
				[Wert]  ASC,
				[DatumZeit]  ASC
				);

				CREATE UNIQUE INDEX [IDX_VARIABLE_] ON [Variable](
				[IPSID]  ASC,
				[ID]  ASC
				);";
	$db->query($select);
	echo $dbpfad. " erfolgreich initialisiert.<br>";

	//nun muss der Pfad zur Datenbank den Verwaltungstool mitgeteilt werden. Dazu wird eine Datei Namens sqlitebasis.php erzeugt
	//die den Pfad zur Datenbank enthält
	$datei = fopen("sqlitebasis.php", "w");
	$basiseintrag = "<?
						//Name der Datenbankdatei
						\$dbpfad=\"".$dbpfad."\";
						//Pfad zur JpGraph Bibliothek
						\$jpgraphpfad=\"".dirname(__FILE__)."\\JpGraph Bib\";


					?>";
	fwrite($datei, $basiseintrag);
	fclose($datei);
	echo "sqlitebasis.php erfolgreich angelegt.<br>";

	//nun muss der Pfad zum DUG Tool  dem Verwaltungstool mitgeteilt werden. Dazu wird eine Datei Namens DUGToolbasis.php erzeugt
	//die den Pfad zum DUG Tool Stammverzeichnis enthält
	$datei = fopen("DUGToolbasis.php", "w");
	$basiseintrag = "<?
						//Pfad zum DUG Tool Stammverzeichnis
						\$DUGTOOLPFAD =\"".dirname(__FILE__)."\\\\\";

					?>";
	fwrite($datei, $basiseintrag);
	fclose($datei);
	echo "DUGToolbasis.php erfolgreich angelegt.<br>";
	copy("DUGToolbasis.php",IPS_GetKernelDir()."scripts\DUGToolbasis.php");
	echo "DUGToolbasis.php erfolgreich ins IPS Scriptverzeichnis kopiert.<br>";

	//eigene DUG Tool Kategorie anlegen
	$DUG_ID = IPS_CreateCategory();         // Kategorie anlegen
	IPS_SetName($DUG_ID, ".DUG"); 			// Kategorie benennen
	$DUG_Media_ID = IPS_CreateCategory(); 	// Kategorie anlegen
	IPS_SetName($DUG_Media_ID, "DUG Media");// Kategorie benennen
	IPS_SetParent($DUG_Media_ID,$DUG_ID);
	$select = "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('DUGKategorieID', '".$DUG_ID."'); INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('DUGMediaKategorieID', '".$DUG_Media_ID."');";
    $db->query($select);



	//Nun wird in IPS das Script angelegt, dass die Variablenwerte in der DB aktualisiert
	copy("DBupdate.php",IPS_GetKernelDir()."scripts\DBupdate.php");
	echo "DBupdate.php erfolgreich ins IPS Scriptverzeichnis kopiert.<br>";
	$ScriptID = IPS_CreateScript(0);
	if (IPS_SetScriptFile($ScriptID, "DBupdate.php"))
	{
		echo "DBupdate.php erfolgreich mit der IPS ID ".$ScriptID." in IPS eingebunden.<br>";
		IPS_SetName($ScriptID, "DBupdate SQLite DUG Tool");
		IPS_SetParent($ScriptID,$DUG_ID);

	}
	else
	{
		IPS_DeleteScript($ScriptID,false);
		$ScriptID = 0;
		echo "Probleme beim Einbinden des Scriptes DBupdate.php in IPS.<br>";
	}
	$select = "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('updateScriptID', '".$ScriptID."');";
	$db->query($select);
	copy("Graphenupdate.php",IPS_GetKernelDir()."scripts\Graphenupdate.php");
	$ScriptID = IPS_CreateScript(0);
	if (IPS_SetScriptFile($ScriptID, "Graphenupdate.php"))
	{
		echo "Graphenupdate.php erfolgreich mit der IPS ID ".$ScriptID." in IPS eingebunden.<br>";
		IPS_SetName($ScriptID, "Graphenupdate SQLite DUG Tool");
		$newEventID = IPS_CreateEvent(1);
		IPS_SetEventCyclic($newEventID, 0, 0, 0, 0, 1, 60);   //Jeden Tag alle 60 Sekunden ( jeder Minuter ein mal)
		((float)IPS_GetKernelVersion() >= 2.1) ? IPS_SetParent($newEventID, $ScriptID) : IPS_SetEventScript($newEventID, $ScriptID);
		IPS_SetEventActive($newEventID, true);
		IPS_SetParent($ScriptID,$DUG_ID);
	}
	else
	{
		IPS_DeleteScript($ScriptID,false);
		$ScriptID = 0;
		echo "Probleme beim Einbinden des Scriptes Graphenupdate.php in IPS.<br>";
	}
	$select = "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('GraphenupdateID', '".$ScriptID."');";
	$db->query($select);

	//Den Pfad für die späteren Graphendiagramme anlegen
	$neuerpfad = stripslashes($_REQUEST["graphenpfad"]);

	if (substr($neuerpfad, -1, 1) != "\\") {$neuerpfad = $neuerpfad."\\";}
	mkdir_rek($neuerpfad);
	if (is_dir($neuerpfad))
	{
		echo "Verzeichnis '".$neuerpfad."' erstellt.<br>";
		$select = "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('Graphenpfad', '".$neuerpfad."');";
		$db->query($select);
		$select = "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('erwGraphenpfad', '".dirname(__FILE__)."\\".$neuerpfad."');";
		$db->query($select);
	}
	else
	{
		echo "Das Verzeichnis '".$neuerpfad."' konnte nicht angelegt werden.<br>";
	}

	$select = "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('Erstellungsdatum', '".time()."');"."\r \n";
	$select = $select."INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('dbversion', '161');"."\r \n";
	$select = $select."INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('kopierezuIPSMedia', '0');"."\r \n";
	$select = $select."INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('registriereMedia', '1');"."\r \n";
	$select = $select."INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('erlaubeY2Achse', '1');"."\r \n";
	$select = $select."INSERT INTO Zusatz (Bezeichnung, WERT) VALUES ('GraphenKonfiguration','GraphenKonfiguration_std.php');"."\r \n";
	$db->query($select);
	echo "Grundeinstellungen in Datenbank gespeichert.<br>";

}
else
{
  	echo "<span style=\"font-weight: bold;\">Abbruch! Es konnte keine neue Datenbank angelegt werden</span><br>";
	die ($sqliteerror);
}


$db->close();
?>
<br>
<br>
<center>
<span style="font-weight: bold;">Installation abgeschlossen</span><br>

<br>
<a href="http:./index.php">Zur Startseite</a>

</center>
</body>
</html>

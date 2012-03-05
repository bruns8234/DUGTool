<?
//==================================================================================
// Datei.......: doinstall.php
// Beschreibung: Legt die Datenbank an und setzt die Umgebungsparameter
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 81 $
// zuletzt geändert : 	$Date: 2009-09-27 19:35:27 +0200 (So, 27 Sep 2009) $
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
  <meta name="Dateiname" content="update.php">
  <meta name="Dateiversion" content="1.5">
  <meta name="Dateidatum" content="1.05.2009">
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <link rel="stylesheet" type="text/css" href="./mystyle.css">
</head>
<body>
<center><span style="font-weight: bold;">Updatescript
zum SQLite Datenbank Und Graphen (DUG) Tool</span><br>

<br>
<br>

<?
$neuesteVersionsNr = 152;
include ("sqlitebasis.php");

$db = new SQLite3($dbpfad);

//Hilfsfunktion zum Pfad anlegen
function mkdir_rek($dir)
{
  if (!is_dir($dir))
  {
	mkdir_rek(dirname($dir));
    mkdir($dir,777);
  }
}

//aktuelle DUG Tool Version abfragen
$select = "SELECT Wert FROM Zusatz WHERE Bezeichnung = 'dbversion';";
$query  = $db->query($select);
$DUGVersion = $query->fetchArray();

if ($DUGVersion['Wert'] != $neuesteVersionsNr)
{
	//für den Zeitraum des Updates müssen alle UpdateScriptTrigger deaktiviert werden
	$select = "SELECT Wert FROM Zusatz WHERE Bezeichnung = 'updateScriptID';";
	$query  = $db->query($select);
	$test   = $query->fetchArray();
	$IPSUpdateScriptID = $test['Wert'];
	$eventliste = IPS_GetScriptEventList((int)$IPSUpdateScriptID);
	foreach ($eventliste as $eventID)
	{
		IPS_SetEventActive($eventID, false); //Triggerevent deaktivieren
	}

	if ($DUGVersion['Wert'] != 1)
	{
		//für den Zeitraum des Updates wird der GraphupdateTrigger deaktiviert
		$select = "SELECT Wert FROM Zusatz WHERE Bezeichnung = 'GraphenupdateID';";
		$query  = $db->query($select);
		$test   = $query->fetchArray();
		$GraphupdateScriptID = $test['Wert'];
		$graphupdateEvent = IPS_GetScriptEventList((int)$GraphupdateScriptID);
		foreach ($graphupdateEvent as $eventID)
		{
			IPS_SetEventActive($eventID, false); //Triggerevent deaktivieren
		}

	}

	//je nach Versionsnummer das update durchführen
	if ($DUGVersion['Wert'] == 1)
	{
			echo "Sie benutzen DUG Tool Version 1.0.<br> Update wird durchgeführt.<br>";
			// neue Optionen in die Datenbank aufnehmen
			$select = "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('kopierezuIPSMedia', '0');"."\r \n";
			$select = $select."INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('registriereMedia', '1');"."\r \n";
			$select = $select."INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('erlaubeY2Achse', '1');"."\r \n";
			$select = $select."INSERT INTO Zusatz (Bezeichnung, WERT) VALUES ('GraphenKonfiguration','GraphenKonfiguration_std.php');"."\r \n";
			$select = $select."UPDATE Zusatz SET Wert = '16' WHERE Bezeichnung = 'dbversion' ;"."\r \n";
			$db->query($select);

			$updateTable = "CREATE TABLE temp_table AS SELECT * FROM Graphenliste;
							DROP TABLE Graphenliste;
							CREATE TABLE [Graphenliste] (
							[VarID] INTEGER  NOT NULL,
							[GraphID] INTEGER  NOT NULL,
							[VarFarbe] TEXT  NOT NULL,
							[DarGanzzahl] BOOLEAN DEFAULT '0' NOT NULL,
							[LinienTyp] INTEGER DEFAULT '1' NOT NULL);
							INSERT INTO Graphenliste (VarID, GraphID, VarFarbe) SELECT * from temp_table;
							DROP TABLE temp_table;";
			$db->query($updateTable);

			//Update auf 1.5
			$updateTable = "CREATE TABLE temp_table AS SELECT * FROM Graph;
							DROP TABLE Graph;
							CREATE TABLE [Graph] (
							[ID] INTEGER  PRIMARY KEY NOT NULL,
							[Name] TEXT  NOT NULL,
							[Ueberschrift] TEXT  NULL,
							[ErstellungsIntervall] INTEGER  NOT NULL,
							[ErstellungsOffset] INTEGER  NOT NULL,
							[GroesseX] INTEGER DEFAULT '1' NOT NULL,
							[GroesseY] INTEGER DEFAULT '1' NOT NULL,
							[DatenIntervall] INTEGER DEFAULT '1' NOT NULL,
							[GraphenTyp] INTEGER DEFAULT '0' NOT NULL,
							[BarGraphIntervall] INTEGER DEFAULT '0' NOT NULL);
							INSERT INTO Graph (ID, Name, Ueberschrift, ErstellungsIntervall, ErstellungsOffset, GroesseX, GroesseY, DatenIntervall) SELECT * from temp_table;
							DROP TABLE temp_table;";
			$db->query($updateTable);

			$updateTable = "CREATE TABLE temp_table AS SELECT * FROM Graphenliste;
			DROP TABLE Graphenliste;
			CREATE TABLE [Graphenliste] (
				[VarID] INTEGER  NOT NULL,
				[GraphID] INTEGER  NOT NULL,
				[VarFarbe] TEXT  NOT NULL,
				[DarGanzzahl] BOOLEAN DEFAULT '0' NOT NULL,
				[LinienTyp] INTEGER DEFAULT '1' NOT NULL,
				[ZWert] INTEGER DEFAULT '1' NOT NULL,
				[Filter] TEXT DEFAULT 'keinFilter' NOT NULL
				);
			INSERT INTO Graphenliste (VarID, GraphID, VarFarbe, DarGanzzahl, LinienTyp) SELECT * from temp_table;
			DROP TABLE temp_table;";
			$db->query($updateTable);


			//frage alle Variablen ab, die irgendeinem Graphen zugewiesen sind und nicht vom Typ float sind
			//für diese variablen wird dann der neu hinzugekommene Wert DarGanzzahl angepasst
			//Standardmäßig sollen Boolean und Integer als Ganzzahl dargestellt werden
			$graphselect = "SELECT DISTINCT Graphenliste.VarID AS 'ID' FROM Graphenliste, Variable WHERE Graphenliste.VarID = Variable.ID AND Variable.Typ != '2';";
			$graphresult = $db->query($graphselect);
			$updateselect = "";
			while($grapheintrag = $graphresult->fetchArray())
			{
				$updateselect = $updateselect."UPDATE Graphenliste SET DarGanzzahl = '1' WHERE VarID = '".$grapheintrag['ID']."';"."\r \n";
			}
			if ($updateselect != "") {$db->query($updateselect);}
			echo "<br>Datenbankupdate erfolgreich durchgeführt.<br>";

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

			//die neuen Versionen von DBupdate.php und Graphenupdate.php werden in den IPS Scriptordner kopiert
			copy("DBupdate.php",IPS_GetKernelDir()."scripts\DBupdate.php");
			copy("Graphenupdate.php",IPS_GetKernelDir()."scripts\Graphenupdate.php");

			//nicht mehr benötigte Dateien löschen
			unlink(IPS_GetKernelDir()."scripts\graphenbasis.php");
			unlink(IPS_GetKernelDir()."scripts\sqlitebasis.php");

			//eigene DUG Tool Kategorie anlegen
			$DUG_ID = IPS_CreateCategory();         // Kategorie anlegen
			IPS_SetName($DUG_ID, ".DUG"); 			// Kategorie benennen
			$DUG_Media_ID = IPS_CreateCategory(); 	// Kategorie anlegen
			IPS_SetName($DUG_Media_ID, "DUG Media");// Kategorie benennen
			IPS_SetParent($DUG_Media_ID,$DUG_ID);
			IPS_SetParent((int)$IPSUpdateScriptID,$DUG_ID); 		//DBUpdate.php Scriptnamen in IPS DUG Ordner verschieben
			sqlite_query($dblink, "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('DUGKategorieID', '".$DUG_ID."'); INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('DUGMediaKategorieID', '".$DUG_Media_ID."');");




			//Graphenupdate wird in IPS neu angelegt, um die ScriptID speichern zu können
			$ScriptID = IPS_CreateScript(0);
			if (IPS_SetScriptFile($ScriptID, "Graphenupdate.php"))
			{
				echo "Graphenupdate.php erfolgreich mit der IPS ID ".$ScriptID." in IPS eingebunden.<br>";
				IPS_SetName($ScriptID, "Graphenupdate SQLite DUG Tool");
				$newEventID = IPS_CreateEvent(1);
				IPS_SetEventCyclic($newEventID, 0, 0, 0, 0, 1, 60);   //Jeden Tag alle 60 Sekunden ( jeder Minuter ein mal)
				IPS_SetEventScript($newEventID, $ScriptID);
				IPS_SetEventActive($newEventID, true);
				IPS_SetParent($ScriptID,$DUG_ID); //Scriptnamen in DUG Ordner verschieben
				sqlite_query($dblink, "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('GraphenupdateID', '".$ScriptID."');");
			}
			else
			{
				IPS_DeleteScript($ScriptID,false);
				echo "Probleme beim Einbinden des Scriptes Graphenupdate.php in IPS.<br>";
			}


			echo "<br>Die Dateien DBupdate.php und Graphupdate.php wurden erfolgreich auf den neuesten Stand gebracht.<br>";
			echo "<br>Die Dateien sqlitebasis.php und graphenbasis.php werden im Script Ordner nicht weiter benötigt und wurden gelöscht.<br>";

			echo "<span style=\"font-weight: bold;\">Update auf Version 1.61 abgeschlossen</span>";
	}

	if ($DUGVersion['Wert'] == 12)
	{
			echo "Sie benutzen DUG Tool Version 1.2.<br> Update wird durchgeführt.<br>";

			$select = "UPDATE Zusatz SET Wert = '16' WHERE Bezeichnung = 'dbversion';"."\r \n";
			$select = $select."UPDATE Zusatz SET Wert = '1' WHERE Bezeichnung = 'registriereMedia';"."\r \n";
			$select = $select."INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('erlaubeY2Achse', '1');"."\r \n";
			$select = $select."INSERT INTO Zusatz (Bezeichnung, WERT) VALUES ('GraphenKonfiguration','GraphenKonfiguration_std.php');"."\r \n";

			//Update auf 1.5
			$updateTable = "CREATE TABLE temp_table AS SELECT * FROM Graph;
							DROP TABLE Graph;
							CREATE TABLE [Graph] (
							[ID] INTEGER  PRIMARY KEY NOT NULL,
							[Name] TEXT  NOT NULL,
							[Ueberschrift] TEXT  NULL,
							[ErstellungsIntervall] INTEGER  NOT NULL,
							[ErstellungsOffset] INTEGER  NOT NULL,
							[GroesseX] INTEGER DEFAULT '1' NOT NULL,
							[GroesseY] INTEGER DEFAULT '1' NOT NULL,
							[DatenIntervall] INTEGER DEFAULT '1' NOT NULL,
							[GraphenTyp] INTEGER DEFAULT '0' NOT NULL,
							[BarGraphIntervall] INTEGER DEFAULT '0' NOT NULL);
							INSERT INTO Graph (ID, Name, Ueberschrift, ErstellungsIntervall, ErstellungsOffset, GroesseX, GroesseY, DatenIntervall) SELECT * from temp_table;
							DROP TABLE temp_table;";
			//sqlite_query($dblink,$updateTable);
			$select = $select + $updateTable;

			$updateTable = "CREATE TABLE temp_table AS SELECT * FROM Graphenliste;
			DROP TABLE Graphenliste;
			CREATE TABLE [Graphenliste] (
				[VarID] INTEGER  NOT NULL,
				[GraphID] INTEGER  NOT NULL,
				[VarFarbe] TEXT  NOT NULL,
				[DarGanzzahl] BOOLEAN DEFAULT '0' NOT NULL,
				[LinienTyp] INTEGER DEFAULT '1' NOT NULL,
				[ZWert] INTEGER DEFAULT '1' NOT NULL,
				[Filter] TEXT DEFAULT 'keinFilter' NOT NULL
				);
			INSERT INTO Graphenliste (VarID, GraphID, VarFarbe, DarGanzzahl, LinienTyp) SELECT * from temp_table;
			DROP TABLE temp_table;";
			$select = $select + $updateTable;


			$db->query($select);
			echo "Datenbank aktualisiert.<br>";

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

			//die neuen Versionen von DBupdate.php und Graphenupdate.php werden in den IPS Scriptordner kopiert
			copy("DBupdate.php",IPS_GetKernelDir()."scripts\DBupdate.php");
			copy("Graphenupdate.php",IPS_GetKernelDir()."scripts\Graphenupdate.php");

			//nicht mehr benötigte Dateien löschen
			unlink(IPS_GetKernelDir()."scripts\graphenbasis.php");
			unlink(IPS_GetKernelDir()."scripts\sqlitebasis.php");

			//eigene DUG Tool Kategorie anlegen
			$DUG_ID = IPS_CreateCategory();         // Kategorie anlegen
			IPS_SetName($DUG_ID, ".DUG"); 			// Kategorie benennen
			$DUG_Media_ID = IPS_CreateCategory(); 	// Kategorie anlegen
			IPS_SetName($DUG_Media_ID, "DUG Media");// Kategorie benennen
			IPS_SetParent($DUG_Media_ID,$DUG_ID);
			IPS_SetParent((int)$IPSUpdateScriptID,$DUG_ID); 		//DBUpdate.php Scriptnamen in IPS DUG Ordner verschieben
			IPS_SetParent((int)$GraphupdateScriptID,$DUG_ID); 		//Graphenupdate.php Scriptnamen in IPS DUG Ordner verschieben

			$select = "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('DUGKategorieID', '".$DUG_ID."'); INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('DUGMediaKategorieID', '".$DUG_Media_ID."');";
            $db->query($select);


			echo "<br>Die Dateien DBupdate.php und Graphupdate.php wurden erfolgreich auf den neuesten Stand gebracht.<br>";
			echo "<br>Die Dateien sqlitebasis.php und graphenbasis.php werden im Script Ordner nicht weiter benötigt und wurden gelöscht.<br>";


			echo "<span style=\"font-weight: bold;\">Update auf Version 1.61 abgeschlossen</span>";
	}

	if ($DUGVersion['Wert'] == 13)
	{
			echo "Sie benutzen DUG Tool Version 1.3.<br> Update wird durchgeführt.<br><br>";

			$select = "UPDATE Zusatz SET Wert = '16' WHERE Bezeichnung = 'dbversion';"."\r \n";
			$select = $select."UPDATE Zusatz SET Wert = '1' WHERE Bezeichnung = 'registriereMedia';"."\r \n";
			$select = $select."INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('erlaubeY2Achse', '1');"."\r \n";
			$select = $select."INSERT INTO Zusatz (Bezeichnung, WERT) VALUES ('GraphenKonfiguration','GraphenKonfiguration_std.php');"."\r \n";

			//Update auf 1.5
			$updateTable = "CREATE TABLE temp_table AS SELECT * FROM Graph;
				DROP TABLE Graph;
				CREATE TABLE [Graph] (
				[ID] INTEGER  PRIMARY KEY NOT NULL,
				[Name] TEXT  NOT NULL,
				[Ueberschrift] TEXT  NULL,
				[ErstellungsIntervall] INTEGER  NOT NULL,
				[ErstellungsOffset] INTEGER  NOT NULL,
				[GroesseX] INTEGER DEFAULT '1' NOT NULL,
				[GroesseY] INTEGER DEFAULT '1' NOT NULL,
				[DatenIntervall] INTEGER DEFAULT '1' NOT NULL,
				[GraphenTyp] INTEGER DEFAULT '0' NOT NULL,
				[BarGraphIntervall] INTEGER DEFAULT '0' NOT NULL);
				INSERT INTO Graph (ID, Name, Ueberschrift, ErstellungsIntervall, ErstellungsOffset, GroesseX, GroesseY, DatenIntervall) SELECT * from temp_table;
				DROP TABLE temp_table;";
			//sqlite_query($dblink,$updateTable);
			$select = $select + $updateTable;

			$updateTable = "CREATE TABLE temp_table AS SELECT * FROM Graphenliste;
			DROP TABLE Graphenliste;
			CREATE TABLE [Graphenliste] (
				[VarID] INTEGER  NOT NULL,
				[GraphID] INTEGER  NOT NULL,
				[VarFarbe] TEXT  NOT NULL,
				[DarGanzzahl] BOOLEAN DEFAULT '0' NOT NULL,
				[LinienTyp] INTEGER DEFAULT '1' NOT NULL,
				[ZWert] INTEGER DEFAULT '1' NOT NULL,
				[Filter] TEXT DEFAULT 'keinFilter' NOT NULL
				);
			INSERT INTO Graphenliste (VarID, GraphID, VarFarbe, DarGanzzahl, LinienTyp) SELECT * from temp_table;
			DROP TABLE temp_table;";
			sqlite_query($dblink,$updateTable);


			$db->query($select);
			echo "Datenbank aktualisiert.<br>";

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

			//die neuen Versionen von DBupdate.php und Graphenupdate.php werden in den IPS Scriptordner kopiert
			copy("DBupdate.php",IPS_GetKernelDir()."scripts\DBupdate.php");
			copy("Graphenupdate.php",IPS_GetKernelDir()."scripts\Graphenupdate.php");

			//nicht mehr benötigte Dateien löschen
			unlink(IPS_GetKernelDir()."scripts\graphenbasis.php");
			unlink(IPS_GetKernelDir()."scripts\sqlitebasis.php");

			//eigene DUG Tool Kategorie anlegen
			$DUG_ID = IPS_CreateCategory();         // Kategorie anlegen
			IPS_SetName($DUG_ID, ".DUG"); 			// Kategorie benennen
			$DUG_Media_ID = IPS_CreateCategory(); 	// Kategorie anlegen
			IPS_SetName($DUG_Media_ID, "DUG Media");// Kategorie benennen
			IPS_SetParent($DUG_Media_ID,$DUG_ID);
			IPS_SetParent((int)$IPSUpdateScriptID,$DUG_ID); 		//DBUpdate.php Scriptnamen in IPS DUG Ordner verschieben
			IPS_SetParent((int)$GraphupdateScriptID,$DUG_ID); 		//Graphenupdate.php Scriptnamen in IPS DUG Ordner verschieben

			$select = "INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('DUGKategorieID', '".$DUG_ID."'); INSERT INTO Zusatz (Bezeichnung, Wert) VALUES ('DUGMediaKategorieID', '".$DUG_Media_ID."');";
			$db->query($select);


			echo "<br>Die Dateien DBupdate.php und Graphupdate.php wurden erfolgreich auf den neuesten Stand gebracht.<br>";
			echo "<br>Die Dateien sqlitebasis.php und graphenbasis.php werden im Script Ordner nicht weiter benötigt und wurden gelöscht.<br>";


			echo "<span style=\"font-weight: bold;\">Update auf Version 1.61 abgeschlossen</span>";
	}

	if ($DUGVersion['Wert'] == 14)
	{
			echo "Sie benutzen DUG Tool Version 1.4.<br> Update wird durchgeführt.<br><br>";

			$select = "UPDATE Zusatz SET Wert = '16' WHERE Bezeichnung = 'dbversion';"."\r \n";

			//Update auf 1.5
			$updateTable = "CREATE TABLE temp_table AS SELECT * FROM Graph;
				DROP TABLE Graph;
				CREATE TABLE [Graph] (
				[ID] INTEGER  PRIMARY KEY NOT NULL,
				[Name] TEXT  NOT NULL,
				[Ueberschrift] TEXT  NULL,
				[ErstellungsIntervall] INTEGER  NOT NULL,
				[ErstellungsOffset] INTEGER  NOT NULL,
				[GroesseX] INTEGER DEFAULT '1' NOT NULL,
				[GroesseY] INTEGER DEFAULT '1' NOT NULL,
				[DatenIntervall] INTEGER DEFAULT '1' NOT NULL,
				[GraphenTyp] INTEGER DEFAULT '0' NOT NULL,
				[BarGraphIntervall] INTEGER DEFAULT '0' NOT NULL);
				INSERT INTO Graph (ID, Name, Ueberschrift, ErstellungsIntervall, ErstellungsOffset, GroesseX, GroesseY, DatenIntervall) SELECT * from temp_table;
				DROP TABLE temp_table;";
				$db->query($updateTable);

			$updateTable = "CREATE TABLE temp_table AS SELECT * FROM Graphenliste;
			DROP TABLE Graphenliste;
			CREATE TABLE [Graphenliste] (
				[VarID] INTEGER  NOT NULL,
				[GraphID] INTEGER  NOT NULL,
				[VarFarbe] TEXT  NOT NULL,
				[DarGanzzahl] BOOLEAN DEFAULT '0' NOT NULL,
				[LinienTyp] INTEGER DEFAULT '1' NOT NULL,
				[ZWert] INTEGER DEFAULT '1' NOT NULL,
				[Filter] TEXT DEFAULT 'keinFilter' NOT NULL
				);
			INSERT INTO Graphenliste (VarID, GraphID, VarFarbe, DarGanzzahl, LinienTyp) SELECT * from temp_table;
			DROP TABLE temp_table;";
			$db->query($updateTable);


			$db->query($select);
			echo "Datenbank aktualisiert.<br>";

			echo "<span style=\"font-weight: bold;\">Update auf Version 1.61 abgeschlossen</span>";
	}

	if ($DUGVersion['Wert'] == 15)
	{
			echo "Sie benutzen DUG Tool Version 1.5.<br> Update wird durchgeführt.<br><br>";

			$select = "UPDATE Zusatz SET Wert = '16' WHERE Bezeichnung = 'dbversion';"."\r \n";
			$db->query($select);

			echo "Datenbank aktualisiert.<br>";

			echo "<span style=\"font-weight: bold;\">Update auf Version 1.61 abgeschlossen</span>";
	}

	if ($DUGVersion['Wert'] == 16)
	{
			echo "Sie benutzen DUG Tool Version 1.6.<br> Update wird durchgeführt.<br><br>";

			$select = "UPDATE Zusatz SET Wert = '16' WHERE Bezeichnung = 'dbversion';"."\r \n";
			$db->query($select);

			echo "Datenbank aktualisiert.<br>";

			echo "<span style=\"font-weight: bold;\">Update auf Version 1.61 abgeschlossen</span>";
	}





	//alle Triggerevents des IPSUpdateScriptes wieder aktivieren
	foreach ($eventliste as $eventID)
	{
		IPS_SetEventActive($eventID, true); //Triggerevent aktivieren
	}
		if ($DUGVersion['Wert'] != 1)
	{
		//GraphupdateTrigger  wird wieder reaktiviert
		$graphupdateEvent = IPS_GetScriptEventList((int)$GraphupdateScriptID);
		foreach ($graphupdateEvent as $eventID)
		{
			IPS_SetEventActive($eventID, true); //Triggerevent deaktivieren
		}
	}

}
else
{
	echo "Ihre DUG Tool Version ist bereits auf dem neuesten Stand.<br> Es wird kein Update durchgeführt.<br>";
}

$db->close();
?>
<br>
<br>
<br>
<br>
<a href="http:./index.php">Zur Startseite</a>

</center>
</body>
</html>

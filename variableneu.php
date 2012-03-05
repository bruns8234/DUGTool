<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="variableneu.php">
    <meta name="Dateiversion" content="1.1">
    <meta name="Dateidatum"   content="28.03.2009">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
  <body>
  <center>
  <span style="font-weight: bold;">SQLite DUG Tool<br>
  Variablen Aufzeichnung erstellen </span><br><br><br>
<?
//==================================================================================
// Datei.......: variableneu.php
// Beschreibung: fügt die ausgewählten Variablen zur Triggerliste des DBupdate Scriptes hinzu
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 81 $
// zuletzt geändert : 	$Date: 2009-09-27 19:35:27 +0200 (So, 27 Sep 2009) $
// Author:			$Author: tobias $
//
// 23.10.2011 TGUSI74
// + SQLite3 Anpassungen
//==================================================================================
include ("sqlitebasis.php");
$db = new SQLite3($dbpfad);

if (isset($_REQUEST['varselection']))
{

	$select = "SELECT * FROM Zusatz WHERE Bezeichnung = 'updateScriptID' ";
	$query = $db->query($select);
	$result = $query->fetchArray();
	$scriptID = (int)$result['Wert'];
	$scriptevent = IPS_GetScriptEventList($scriptID);
	$j=1;
	foreach($scriptevent as $eventID)
	{
		$eventData = IPS_GetEvent($eventID);
		$watchedVars[$j] = (int)$eventData[((float)IPS_GetKernelVersion() >= 2.1) ? "TriggerVariableID" : "TriggerVariable"];
		$j=$j+1;
	}




	reset($_REQUEST['varselection']);
	foreach ($_REQUEST['varselection'] as $k => $v)
	{
		$IPS_VarID = (int)$v;
		if (IPS_VariableExists($IPS_VarID))
		{
			//die IPS Variable in der DB suchen
			$select = "SELECT * FROM Variable WHERE IPSID = ".$IPS_VarID;
			$result = $db->query($select);
			$varindb = $result->fetchArray();

			//Wenn sie noch nicht in der DB ist, muss sie angelegt werden
			if (!isset($varindb['ID']))
			{
				//für später mal. evtl aus dem Zusatztext die Einheit extrahieren ( nur ne Idee)
				$obj = IPS_GetObject($IPS_VarID);
				$varName = $obj['ObjectName'];
				$varText = $obj['ObjectInfo'];

				//Variablentyp abfragen (Boolean, Integery, Float, String)
				$thisvar = IPS_GetVariable($IPS_VarID);
				$varType = $thisvar['VariableValue']['ValueType'];

				$varEinheit = "unbekannt";
				$varPfad = IPS_GetLocation($IPS_VarID);

				//Variable in der DB neu anlegen
				//als name der Variable, wird der Pfad mit dem Namen der Variablen eingetragen. Der DB Eintrag Name wird
				//auch in der Legende des Graphen benutzt.
				//Der Eintrag Name soll später mal veränderbar sein, während Pfad immer zur Variable gehört
				$select = "INSERT INTO Variable  (IPSID, Name, Typ, Einheit, MaxIntervallZeit, MaxAnzahl) VALUES ('".$IPS_VarID."','".$varPfad."','".$varType."','".$varEinheit."', 0, 0);";
				$db->query($select);
				$varrowid = $db->lastInsertRowID();

				$select = "INSERT INTO VarEreignis (DatumZeit, Wert, VarID) VALUES (".time().", '".GetValue($IPS_VarID)."','".$varrowid."'); ";
				$db->query($select);
				echo "<br>Die ausgew&auml;hlte Variable mit der ID ".$v." wurde in der Datenbank angelegt.<br>";

			}


			// es wird überprüft, ob die Variable schon in der IPS Triggerliste von DBupdate.php steht
			if (($j != 1)&&(array_search($IPS_VarID, $watchedVars)))
			{
				echo "Die ausgew&auml;hlte Variable mit der ID ".$IPS_VarID." wird bereits aufgezeichnet.<br>";
			}
			else
			{
					$newEventID = IPS_CreateEvent(0);
					IPS_SetEventTrigger($newEventID, 1,$IPS_VarID);
					//Abfrage ob die Zuweisung geklappt hat. Für den Fall, das $scriptID die falsche oder keine IPSID enthält
					//soll das angelegte Ereignis gelöscht werden, damit kein unzugewiesenes Ereignis in IPS rumgeistert
					if(((float)IPS_GetKernelVersion() >= 2.1) ? IPS_SetParent($newEventID, $scriptID) : IPS_SetEventScript($newEventID, $scriptID))
					{
						IPS_SetEventActive($newEventID, true);
						echo "Die Werte der ausgew&auml;hlte Variable mit der ID ".$IPS_VarID." werden nun aufgezeichnet.<br>";
					}
					else
					{
						IPS_DeleteEvent($newEventID);
						echo "Die Variable mit der ID ".$IPS_VarID." konnte dem DBupdate Script nicht zugewiesen werden.<br>
						&Uuml;Pr&uuml;fen Sie bitte in den Einstellungen, ob die IPS Script ID von DBupdate.php mit denen in der
						DUG Tool DB &uuml;bereinstimmt.";
					}

			}
		}
		else
		{
			echo "Die ausgew&auml;hlte Variable mit der ID ".$IPS_VarID." ist in IPS nicht (mehr) vorhanden.<br>";
		}

	}
	$scriptevent = IPS_GetScriptEventList($scriptID);
	echo "<br><br>Es werden nun ".count($scriptevent)." Variablen aufgezeichnet.<br>";
}
else
{
	echo "<b> Es wurde keine Variable ausgewählt.</b>";
}





?>

		<br>

		<br>


		</b>
		<br>



		<br>
				<a href="http:./verwaltung.php">zur&uuml;ck zur Verwaltungsansicht</a><br>
		<a href="http:./index.php">zur&uuml;ck zur Startseite</a>
		</center>
<?
$db->close();
?>
	</body>
</HTML>


<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="variableentf.php">
    <meta name="Dateiversion" content="1.0">
    <meta name="Dateidatum"   content="28.03.2009">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
  <body>
  <center>
  <span style="font-weight: bold;">SQLite DUG Tool</span><br>
  Variablen Aufzeichnung l&ouml;schen <br><br><br>
<?
//==================================================================================
// Datei.......: variableentf.php
// Beschreibung: entfernt die ausgewählten Variablen aus der  Triggerliste des DBupdate Scriptes
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
	foreach($scriptevent as $eventID)
	{
		$eventData = IPS_GetEvent($eventID);
		$watchedVars[$eventID] = (int)$eventData[((float)IPS_GetKernelVersion() >= 2.1) ? "TriggerVariableID" : "TriggerVariable"];
	}




	reset($_REQUEST['varselection']);
	foreach ($_REQUEST['varselection'] as $k => $v)
	{
		if (IPS_VariableExists((int)$v))
		{
			$eventID = array_search($v, $watchedVars);
			if ($eventID)
			{
				IPS_DeleteEvent((int)$eventID);
				echo "Die ausgew&auml;hlte Variable mit der ID ".$v." wird nun nicht weiter aufgezeichnet.<br>";
			}
			else
			{
				echo "Die ausgew&auml;hlte Variable mit der ID ".$v." wurde nicht aufgezeichnet und wird daher nicht entfernt.<br>";
			}
		}
		else
		{
			echo "Die ausgew&auml;hlte Variable mit der ID ".$v." ist in IPS nicht (mehr) vorhanden.<br>";
		}

	}
	$scriptevent = IPS_GetScriptEventList($scriptID);
	echo "<br><br>Es werden nun ".count($scriptevent)." Variablen aufgezeichnet.<br>";
}
else
{
	echo "<b> Es wurde keine Variable ausgew&auml;hlt.</b>";
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


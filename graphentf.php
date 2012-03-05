<?
//==================================================================================
// Datei.......: graphentf.php
// Beschreibung: entfernt die ausgewählten Graphen aus der DB
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 40 $
// zuletzt geändert : 	$Date: 2009-05-29 15:53:44 +0200 (Fr, 29 Mai 2009) $
// Author:			$Author: tobias $
//
// 23.10.2011 TGUSI74
// + SQLite3 Anpassungen
//==================================================================================
include ("sqlitebasis.php");
$db = new SQLite3($dbpfad);


global $varcount;

?>
<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="graphentf.php">
    <meta name="Dateiversion" content="1.0">
    <meta name="Dateidatum"   content="25.03.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
  <body>
  <center><span style="font-weight: bold;">SQLite DUG Tool<br>
Graphen entfernen</span>
		<br>
		<br>
		<br>
<?	if (isset($_REQUEST['graphenauswahl']))
	{
		reset($_REQUEST['graphenauswahl']);
		foreach ($_REQUEST['graphenauswahl'] as $k => $v)
		{
			$select = "SELECT Name FROM Graph WHERE ID =".$v.";";
			$query = $db->query($select);
			$temp = $query->fetchArray();
			$graphname = $temp['Name'];

			$select = "DELETE FROM Graph WHERE ID =".$v.";";
			$query = $db->query($select);

			$select = "DELETE FROM Graphenliste WHERE GraphID=".$v.";";
			$query = $db->query($select);

			echo "Der Graph '".$graphname."' wurde geloescht. <br>";
		}
	} else {
		echo "Es wurde kein Graph ausgew&auml;hlt.";
}
?>
		<br>
		<br>
<?
//fragt die Anzahl der vorhandenen Graphen ab
$select = "SELECT COUNT(ID) AS Anzahl FROM Graph;";
$query = $db->query($select);
$varcount = $query->fetchArray();
echo "Sie haben nun ".$varcount['Anzahl']." Graphen in Ihrer Datenbank.";

?>
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


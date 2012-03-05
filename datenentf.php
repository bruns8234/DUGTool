<?
//==================================================================================
// Datei.......: datenentf.php
// Beschreibung: entfernt die Datensätze und Variablen aus der DB
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


?>
<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="datenentf.php">
    <meta name="Dateiversion" content="1.0">
    <meta name="Dateidatum"   content="29.05.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
  <body>
  <center><span style="font-weight: bold;">SQLite DUG Tool<br>
Datensatz entfernen</span>
		<br>
		<br>
		<br>
<?	if (isset($_REQUEST['varselection']))
	{
		reset($_REQUEST['varselection']);
		foreach ($_REQUEST['varselection'] as $k => $v)
		{
			$select = "SELECT ID, Name FROM Variable WHERE IPSID ='".$v."';";
			$query = $db->query($select);
			$temp = $query->fetchArray();
			$varID = $temp['ID'];
			$varName = $temp['Name'];


			$select = "DELETE FROM Variable WHERE ID ='".$varID."';";
			$query = $db->query($select);
			$select = "DELETE FROM VarEreignis WHERE VarID='".$varID."';";
			$query = $db->query($select);
			echo "Die Variable '".$varName."' und alle dazugeh&ouml;rigen Datens&auml;tze und Graphen wurden gel&ouml;scht. <br>";

			$select = "SELECT GraphID FROM Graphenliste WHERE VarID = '".$varID."';";
			$query = $db->query($select);
			while ($graph = $query->fetchArray())
			{
							$select = "SELECT count(VarID) as Anzahl FROM Graphenliste WHERE GraphID = '".$graph['GraphID']."';";
							$countquery = $db->query($select);
							$countresult = $countquery->fetchArray();
							if ($countresult['Anzahl'] == 1)
							{
								$select = "DELETE FROM Graphenliste WHERE VarID='".$varID."' AND GraphID = '".$graph['GraphID']."';";
								$db->query($select);
								$select = "DELETE FROM Graph WHERE ID = '".$graph['GraphID']."';";
								$db->query($select);
								echo "Ein dazugeh&ouml;riger Graph wurden gel&ouml;scht. <br><br>";
							}
							else
							{
								$select = "DELETE FROM Graphenliste WHERE VarID='".$varID."' AND GraphID = '".$graph['GraphID']."';";
								$db->query($select);
							}

			}

		}
	} else {
		echo "Es wurde keine Variable ausgew&auml;hlt.<br>";
}
?>
		<br>
		<br>
<?
//fragt die Anzahl der vorhandenen Variablen ab
$select = "SELECT COUNT(ID) AS Anzahl FROM Variable;";
$query = $db->query($select);
$varcount = $query->fetchArray();
echo "Sie haben nun ".$varcount['Anzahl']." Variablen in Ihrer Datenbank.";

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


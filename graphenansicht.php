<?
//==================================================================================
// Datei.......: graphenerstellen.php
// Beschreibung: Erstellt eine Seite mit den erzeugten Graphen
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
<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="graphenansicht.php">
    <meta name="Dateiversion" content="1.5">
    <meta name="Dateidatum"   content="29.04.2009">
	<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
	<meta http-equiv="Expires" content="0" />
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
<body>
<center><span style="font-weight: bold;">SQLite DUG Tool<br>
Graphenansicht</span>
<br>
<br>
<br>
<?
include ("graphenbasis.php");

$time_start = microtime(true);
//$dblink = dbopen();
$db = new SQLite3($dbpfad);

//Namen alle Graphen erfragen um den Dateinamen zu bestimmen
$select = "SELECT * FROM Zusatz WHERE Bezeichnung = 'Graphenpfad';";
$zusatzresult = $db->query($select);
$pfadeintrag  = $zusatzresult->fetchArray();

//Pfad zu den Graphdiagrammen erfragen
$select = "SELECT * FROM Graph ORDER BY ID ASC;";
$graphresult = $db->query($select);

while($grapheintrag = $graphresult->fetchArray())
{
	$mygraph = $pfadeintrag['Wert'].$grapheintrag['Name']."_".$grapheintrag['ID'].".png";
	if (file_exists($mygraph))
	{
		echo "<img src=".rawurlencode($mygraph)."><BR><BR>";
	}
	else
		{
		echo "Der Graph '".$grapheintrag['Name']."' wurde noch nicht erstellt.<BR><BR>";
	}
}

$db->close();

?>
	<br>



	<br>
	<a href="http:./verwaltung.php">zur&uuml;ck zur Verwaltungsansicht</a><br>
	<a href="http:./index.php">zur&uuml;ck zur Startseite</a>
</center>
</body>
</html>
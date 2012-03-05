<?
//==================================================================================
// Datei.......: variablebearb.php
// Beschreibung: Übernimmt die aktuallisierten Werte der Variablen in die Datenbank
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
    <meta name="Dateiname"    content="variablebearb.php">
    <meta name="Dateiversion" content="1.0">
    <meta name="Dateidatum"   content="25.03.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
	<meta http-equiv="cache-control" content="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
<body>
<center><span style="font-weight: bold;">SQLite DUG Tool<br>
Variablen&auml;nderungen &uuml;bernehmen  </span>
<br>
<br>
<br>
<?
include ("graphenbasis.php");
//Pfad zu den Graphdiagrammen erfragen

$db = new SQLite3($dbpfad);

$select = "";
if (isset($_REQUEST['variable']))
{
	reset($_REQUEST['variable']);
	foreach ($_REQUEST['variable'] as $k => $v)
	{
		$select = $select."UPDATE Variable SET 'Name' = '".stripslashes($v[1])."', 'Einheit' = '".stripslashes($v[2])."', 'MaxAnzahl' = '".$v[3]."', 'MaxIntervallZeit' = '".$v[4]."' WHERE ID ='".$v[0]."'; ";
		$select = $select."\r \n";

	}
	$db->query($select);
	echo "Alle gemachten &Auml;nderungen wurden in die Datenbank &uuml;bernommen.";

}
else
{
	echo "<b>Es sind keine Variablen zur Bearbeitung vorhanden.</b>";
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
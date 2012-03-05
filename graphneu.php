<?
//==================================================================================
// Datei.......: graphneu.php
// Beschreibung: legt einen neuen Graphen in der DB an und weist ihm alle markierten Variablen zu.
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
?>
<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="graphneu.php">
    <meta name="Dateiversion" content="1.5">
    <meta name="Dateidatum"   content="01.05.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
  <body>
  <span style="font-weight: bold;">SQLite DUG Tool<br>
Graphen hinzuf&uuml;gen</span>
		<br>
		<br>
		<br>
<?


//die DB IDs der Variablen, die vorher ausgewählt worden sind, um zum Graphen zu gehören, befinden sich im Array varselection
if (isset($_REQUEST['varselection']))
{
	//den neuen Graphen anlegen
	$select = "INSERT INTO Graph (Name, Ueberschrift, ErstellungsIntervall, ErstellungsOffset,
	GroesseX, GroesseY, DatenIntervall, GraphenTyp, BarGraphIntervall)
	VALUES (
	'".$_REQUEST["gname"]."',
	'".$_REQUEST["gueberschrift"]."',
	'".(int)$_REQUEST["gerstintervall"]."',
	'".(int)$_REQUEST["gerstoffset"]."',
	'".(int)$_REQUEST["ggroessex"]."',
	'".(int)$_REQUEST["ggroessey"]."',
	'".(int)$_REQUEST["gdarintervall"]."',
	'".(int)$_REQUEST["gTyp"]."',
	'".(int)$_REQUEST["gBarGraphIntervall"]."'
	);";
	$query = $db->query($select);

	//bestimme die ID des neuesten Graphen in der DB. Denn dieser wurde ja gerade hinzugefügt.
	$graphid = $db->lastInsertRowID();
	$select = "";
	$i = 1; //Zähle für den ZWert (Tiefe der Variablen)
	reset($_REQUEST['varselection']);
	foreach ($_REQUEST['varselection'] as $k => $v)
	{
		if (isset($_REQUEST['vardarst'][$k]) && ((($_REQUEST['vardarst'][$k]) == 'on')
			|| (($_REQUEST['vardarst'][$k]) == 1)))
			{
				$vardarst = 1;
			}
			else
			{
				$vardarst = 0;
			}

		$select = $select." INSERT INTO 'Graphenliste' (GraphID, VarID, VarFarbe, DarGanzzahl, LinienTyp, ZWert)
		VALUES (
		'".$graphid."',
		'".$v."',
		'".$_REQUEST['varfarbe'][$k]."',
		'".$vardarst."',
		'".$_REQUEST['varlinie'][$k]."',
		'".$i."'
		);"."\r \n";
		$i++;

	}
	$db->query($select);

	echo "Der Graph ".$_REQUEST['gname']." wurde angelegt.<br>";
}
else
{
	echo "Es ist nicht erlaubt Graphen ohne Variablen anzulegen.<br>";
	echo "Es wurde kein Graph angelegt.<br>";

}


//fragt die Anzahl der vorhandenen Graphen ab
$select = "SELECT COUNT(ID) AS Anzahl FROM Graph;";
$query = $db->query($select);
$varcount = $query->fetchArray();
global $varcount;

$db->close();

?>

		<br>
		<br>
		Sie haben nun <? echo $varcount['Anzahl']; ?> Graphen in Ihrer Datenbank.
		<br>
		<br>
		<a href="http:./graphenanlegeneinfach.php">einen weiteren Graphen anlegen (einfach)</a><br>
		<a href="http:./graphenanlegen.php">einen weiteren Graphen anlegen (erweitert)</a><br>
		<br>
		<a href="http:./verwaltung.php">zur&uuml;ck zur Verwaltungsansicht</a><br>
	<a href="http:./index.php">zur&uuml;ck zur Startseite</a>
		</center>
	</body>
</HTML>


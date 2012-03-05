<?
//==================================================================================
// Datei.......: graphenerstellen.php
// Beschreibung: Erstellt eine Seite mit den erzeugten Graphen
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
<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="graphenerstellen.php">
    <meta name="Dateiversion" content="1.5">
    <meta name="Dateidatum"   content="01.05.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
	<meta http-equiv="cache-control" content="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
<body>
<center><span style="font-weight: bold;">SQLite DUG Tool<br>
Graphen&auml;nderungen &uuml;bernehmen  </span>
<br>
<br>
<br>
<?
include ("graphenbasis.php");

$db = new SQLite3($dbpfad);
$select = "";
if (isset($_REQUEST['gID']))
{
	reset($_REQUEST['gID']);
	foreach ($_REQUEST['gID'] as $k => $v)
	{
		$select = $select."UPDATE Graph SET
		'Name' 					= '".$_REQUEST['gname'][$k]."',
		'Ueberschrift' 			= '".$_REQUEST['gueberschrift'][$k]."',
		'GroesseX' 				= '".(int)$_REQUEST['ggroessex'][$k]."',
		'GroesseY'				= '".(int)$_REQUEST['ggroessey'][$k]."',
		'ErstellungsIntervall' 	= '".(int)$_REQUEST['gerstintervall'][$k]."',
		'DatenIntervall' 		= '".(int)$_REQUEST['gdarintervall'][$k]."',
		'ErstellungsOffset' 	= '".(int)$_REQUEST['gerstoffset'][$k]."',
		'GraphenTyp' 			= '".(int)$_REQUEST['gTyp'][$k]."',
		'BarGraphIntervall' 	= '".(int)$_REQUEST['gBarGraphIntervall'][$k]."'
		WHERE ID ='".$v."'; ";
		$select = $select."\r \n";
		foreach($_REQUEST['varfarbe'][$k] as $varID => $varFarbe)
		{
			if (isset($_REQUEST['vardarst'][$k][$varID]) && ((($_REQUEST['vardarst'][$k][$varID]) == 'on')
			|| (($_REQUEST['vardarst'][$k][$varID]) == 1)))
			{
				$vardarst = 1;
			}
			else
			{
				$vardarst = 0;
			}

			$select = $select."UPDATE Graphenliste SET
			VarFarbe 			= '".$varFarbe."',
			DarGanzzahl 		= '".$vardarst."',
			LinienTyp 			= '".$_REQUEST['varlinie'][$k][$varID]."',
			ZWert				= '".$_REQUEST['varZWert'][$k][$varID]."',
			Filter			= '".$_REQUEST['varFilter'][$k][$varID]."'
			WHERE VarID = '".$varID."' AND GraphID = '".$v."';";
			$select = $select."\r \n";
		}
		//echo $select."<br><br>"; DEBUG Ausgabe
	}
	$db->query($select);
	echo "Alle gemachten &Auml;nderungen wurden in die Datenbank &uuml;bernommen.";

}
else
{
	echo "<b>Es sind keine Graphen zur Bearbeitung vorhanden.</b>";
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
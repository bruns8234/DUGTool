<?
//==================================================================================
// Datei.......: graphverwaltung.php
// Beschreibung: extra Verwaltungsübersicht für alle Graphen
//			Übersicht nur für Graphen um den Aufbau der Verwaltungsseite etwas zu beschleunigen,
//			indem nur die Graphen und nicht noch extra die Variablen angezeigt werden
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 40 $
// zuletzt geändert : 	$Date: 2009-05-29 15:53:44 +0200 (Fr, 29 Mai 2009) $
// Author:			$Author: tobias $
//
// 23.10.2011 TGUSI74
// + SQLite3 Anpassungen
//==================================================================================
require_once("sqlitebasis.php");
require_once("VerwaltungsKonfiguration_std.php");

$db = new SQLite3($dbpfad);
$time_start = microtime(true);
?>
<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="graphverwaltung.php">
    <meta name="Dateiversion" content="1.2">
    <meta name="Dateidatum"   content="05.04.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
 <script language="JavaScript" type="text/JavaScript">
function graphloeschen()
{
	if(confirm('Wollen Sie die / den Graphen wirklich löschen?'))
	{
		document.graphen.action = 'graphentf.php';
		document.graphen.submit();
	}
}
function grapherstellen()
{
		document.graphen.action = 'graphenerstellen.php';
		document.graphen.submit();
}
function graphanlegeneinfach()
{
		document.graphen.action = 'graphenanlegeneinfach.php';
		document.graphen.submit();
}
function graphanlegenerweitert()
{
		document.graphen.action = 'graphenanlegen.php';
		document.graphen.submit();
}
function graphbearbeitenerweitert()
{
		document.graphen.action = 'graphenbearbeiten.php';
		document.graphen.submit();
}
function graphbearbeiteneinfach()
{
		document.graphen.action = 'graphenbearbeiteneinfach.php';
		document.graphen.submit();
}

function markiereAlle()
{
	for(var i = 0; i < document.graphen.elements["anzahl"].value; i++)
	{
		document.graphen.elements[i].checked = document.graphen.elements[document.graphen.elements["anzahl"].value].checked;
	}

}
</script>
  <body>
    <br>
    <center><span style="font-weight: bold;">SQLite DUG Tool<br>
		Graphverwaltung</span><br>
	  	<div style="text-align: right;"><a href="http:./verwaltung.php">zur Verwaltungsansicht</a> <br>
	<a href="http:./index.php">zur&uuml;ck zur Startseite</a><br></div>

      <br>

      <br>
	  <b>Erstellte Graphen</b>
      <br>



      <form name="graphen"  method="post" action="graphentf.php" target="_self">
        <table class="tab_normal" witdh="100%" cellspacing="3" cellpadding="3" >
          <colgroup>
            <col width="5%">
            <col width="30%">
            <col width="30%">
            <col width="10%">
            <col width="10%">
            <col width="10%">
			<col width="5%">
          </colgroup>
          <tr class="tab_headline">
            <td>Markieren</td>
            <td>Name</td>
            <td>&Uuml;berschrift</td>
            <td>Gr&ouml;sse<br>B x H Pixel</td>
            <td>Neuerstellungs-<br>intervall</td>
            <td>Darstellungs-<br>zeitraum</td>
			<td>Variablen</td>
          </tr>
<?
$select = "SELECT * FROM Graph ORDER BY Name ASC";		//alle angelegten Graphen raussuchen
$query = $db->query($select);
$i=0;
while($graphentry = $query->fetchArray())			//alle gefundenen Graphen durchlaufen
	{
			//hier wird  abgefragt, wie viele Variablen der Graph darstellt. Also, wie viele Linien gezeichnet werden
			$select = "SELECT COUNT(VarID) AS Anzahl FROM Graphenliste WHERE GraphID = '".$graphentry['ID']."';";
			$varquery =$db->query($select);
			$varinfo = $varquery->fetchArray();
?>
          <tr class="tab_normal_line">
            <td><center><input type="checkbox" name="graphenauswahl[<? echo $i; ?>]" value="<?echo $graphentry['ID'];?>"></center></td>
            <td><?echo $graphentry['Name'];?></td>
            <td><?echo $graphentry['Ueberschrift'];?></td>
            <td><?echo $graphentry['GroesseX']." x ".$graphentry['GroesseY'];?></td>
            <td><?echo (!isset($NEUERSTELLUNGSINTERVALL[$graphentry['ErstellungsIntervall']]) ? $graphentry['ErstellungsIntervall']." sek": $NEUERSTELLUNGSINTERVALL[$graphentry['ErstellungsIntervall']]);?></td>
            <td><?echo (!isset($DARSTELLUNGSINTERVALL[$graphentry['DatenIntervall']]) ? $graphentry['DatenIntervall']." sek" : $DARSTELLUNGSINTERVALL[$graphentry['DatenIntervall']]);?></td>
			<td><?echo $varinfo['Anzahl'];?></td>
          </tr>

<?	$i++;
	}
?>

		</table>
        <br><input type="hidden" name="anzahl" value="<? echo $i; ?>">
		<table>
			<tr>
				<td><input type="button" name="entf" value="Graphen sofort erstellen" onClick="javascript:grapherstellen();"></td>
				<td><input type="button" name="entf" value="Graphen l&ouml;schen" onClick="javascript:graphloeschen();"></td>
			</tr>
			<tr>
				<td><input type="button" name="entf" value="Graphen anlegen (einfach)" onClick="javascript:graphanlegeneinfach();"></td>
				<td><input type="button" name="entf" value="Graphen bearbeiten (einfach)" onClick="javascript:graphbearbeiteneinfach();"></td>
			</tr>
			<tr>
				<td><input type="button" name="entf" value="Graphen anlegen (erweitert)" onClick="javascript:graphanlegenerweitert();"></td>
				<td><input type="button" name="entf" value="Graphen bearbeiten (erweitert)" onClick="javascript:graphbearbeitenerweitert();"></td>
			</tr>
		</table>

<?
$db->close();

	$time_end = microtime(true);
	$mytime = $time_end - $time_start;
	echo "Graphverwaltungsansicht erstellt in ".round($mytime,2)." Sekunden.<br>";
?>
	</center>
	</form>
  </body>
</html>

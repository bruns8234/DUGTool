<?
//==================================================================================
// Datei.......: variablenverwaltung.php
// Beschreibung: xtra Verwaltungsübersicht für alle Variablen
//			Übersicht nur für Variablen um den Aufbau der Verwaltungsseite etwas zu beschleunigen,
//			indem nur die Variablen und nicht noch extra die Variablen angezeigt werden
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 81 $
// zuletzt geändert : 	$Date: 2009-09-27 19:35:27 +0200 (So, 27 Sep 2009) $
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
    <meta name="Dateiname"    content="variablenverwaltung.php">
    <meta name="Dateiversion" content="1.2">
    <meta name="Dateidatum"   content="05.04.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
 <script language="JavaScript" type="text/JavaScript">
function variablehinzu()
{
		document.IPSvar.action = 'variableneu.php';
		document.IPSvar.submit();
}

function variableentf()
{
		document.IPSvar.action = 'variableentf.php';
		document.IPSvar.submit();
}

function variablebearb()
{
		document.IPSvar.action = 'variablenbearbeitung.php';
		document.IPSvar.submit();
}
function datensatzloeschen()
{
	if(confirm('Wollen Sie die / den Variablen und alle dazugehörigen Datensätze wirklich löschen?'))
	{
		if(confirm('Sie sind sich sicher, dass sie alle Datensätze dieser Variable UNWIEDERBRINGLICH löschen wollen?'))
		{
			document.IPSvar.action = 'datenentf.php';
			document.IPSvar.submit();
		}
	}
}
</script>
  <body>
    <br>
    <center><span style="font-weight: bold;">SQLite DUG Tool<br>
		Variablenverwaltung</span><br>
			<div style="text-align: right;"><a href="http:./verwaltung.php">zur Verwaltungsansicht</a> <br>
	<a href="http:./index.php">zur&uuml;ck zur Startseite</a><br></div>
      <br>
	  <br>


<b>Alle Variablen</b>
<br>

      <form name="IPSvar" method="post" target="_self">
        <table class="tab_normal" witdh="100%" cellspacing="3" cellpadding="3" >
          <colgroup>
            <col width="8%">
			<col width="5%">
			<col width="5%">
            <col width="5%">
            <col width="50%">
            <col width="12%">
            <col width="10%">
            <col width="10%">
          </colgroup>
          <tr class="tab_headline">
            <td>Markieren</td>
			<td>In DB</td>
			<td>Aufz.</td>
            <td>IPS ID</td>
            <td>IPS Name</td>
            <td>Einheit</td>
            <td>Typ</td>
            <td>Datens&auml;tze</td>
          </tr>
<?
//hier wird abgefragt, welche Variablen in der Triggerliste des DBupdateScriptes stehen und damit aufgezeichnet werden
$select = "SELECT * FROM Zusatz WHERE Bezeichnung = 'updateScriptID' ";
$query = $db->query($select);
$result = $query->fetchArray();
$scriptID = (int)$result['Wert'];
$scriptevent = IPS_GetScriptEventList($scriptID);
$j=1;
$watchedVars[0]=0;
foreach($scriptevent as $eventID)
{
	$eventData = IPS_GetEvent($eventID);
	$watchedVars[$j] = (int)$eventData[((float)IPS_GetKernelVersion() >= 2.1) ? "TriggerVariableID" : "TriggerVariable"];
	$j=$j+1;
}


$i =0;
$varindblist[0] = null;

$select = "SELECT * FROM Variable ORDER BY Name ASC;";
$varquery =$db->query($select);
while($dbvarinfo = $varquery->fetchArray())
{
		//abfragen, wie viele Datensätze dieser Variablen in der DB sind
		$select = "SELECT COUNT(ID) AS Anzahl FROM VarEreignis WHERE VarID = '".$dbvarinfo['ID']."';";
		$varcountquery =$db->query($select);
		$varcount = $varcountquery->fetchArray();

		 $vartype = ((int)$dbvarinfo['Typ'] == 0 ? 'Boolean' : '').
                 ((int)$dbvarinfo['Typ'] == 1 ? 'Integer' : '').
                 ((int)$dbvarinfo['Typ'] == 2 ? 'Float' : '').
				 ((int)$dbvarinfo['Typ'] == 3 ? 'String' : '');




?>	   <tr class="tab_normal_line">
		<td><center><input type="checkbox" name="varselection[<? echo $i; ?>]" value="<?echo ((int)$dbvarinfo['IPSID']);?>"><center></td>
		<td>ja</td>
	    <td><?echo (array_search($dbvarinfo['IPSID'], $watchedVars) ? 'ja': 'nein');?></td>
		<td><?echo $dbvarinfo['IPSID'];?></td>
		<td><?echo $dbvarinfo['Name'];?></td>
		<td><?echo $dbvarinfo['Einheit'];?></td>
        <td><?echo $vartype;?></td>
        <td><?echo $varcount['Anzahl'];?></td>
       </tr>


<?
$varindblist[$i] = $dbvarinfo['IPSID'];
$i = $i +1;
}

$variables = IPS_GetVariableList();
foreach($variables as $IPSID) // Jeden Eintrag des Arrays verarbeiten
{
    //hier wird  abgefragt, ob sich die Variable in der DB befindet
	if (array_search($IPSID, $varindblist) === false)
	{
		//wenn nicht, soll sie mit ihrem Namen in diese Liste gespeichert werden  -> zum späteren Sortieren nach Namen
		$myvariablelist[$IPSID] = IPS_GetLocation($IPSID);
	}
}
if (isset($myvariablelist))
{
	asort($myvariablelist,SORT_STRING);
	reset($myvariablelist);
	foreach($myvariablelist as $IPSID => $varname) // Jeden Eintrag des Arrays verarbeiten
	{
		$varindb = false;	//Variable ist nicht in der Datenbank
		$varcount = 0;    //demnach ist  $varcount, also die Anzahl der Datenbankeinträge gleich null
		$varinfo = IPS_GetVariable($IPSID);	// Liefert Infos zur Variable mit der ID $varID
		$vartype = ($varinfo['VariableValue']['ValueType'] == 0 ? 'Boolean' : '').
                 ($varinfo['VariableValue']['ValueType'] == 1 ? 'Integer' : '').
                 ($varinfo['VariableValue']['ValueType'] == 2 ? 'Float' : '').
				 ($varinfo['VariableValue']['ValueType'] == 3 ? 'String' : '');

?>
          <tr class="tab_normal_line">
            <td><center><input type="checkbox" name="varselection[<? echo $i; ?>]" value="<?echo $IPSID;?>"><center></td>
			<td>nein</td>
            <td>nein</td>
			<td><?echo $IPSID;?></td>
			<td><?echo $varname;?></td>

			<td><?echo ($varindb ? $dbvarinfo['Einheit']: 'unbekannt');?></td>
            <td><?echo $vartype;?></td>
            <td>0</td>
          </tr>

<?
	$i = $i +1;
	}
}


$db->close();

?>

        </table>
        <br>

		<input type="button" name="hinzf" value="Variablen hinzuf&uuml;gen" onClick="javascript:variablehinzu();">
	    <input type="button" name="entf" value="Variablen entfernen" onClick="javascript:variableentf();">
		<input type="button" name="bearb" value="Variablen bearbeiten" onClick="javascript:variablebearb();">
		<input type="button" name="loeschen" value="Datensatz aus DB l&ouml;schen" onClick="javascript:datensatzloeschen();">
      </form>
    </center>
<?
	$time_end = microtime(true);
	$mytime = $time_end - $time_start;
	echo "Variablenverwaltungsansicht erstellt in ".round($mytime,2)." Sekunden.<br>";
?>
  </body>
</html>

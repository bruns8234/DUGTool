<?
//==================================================================================
// Datei.......: variablenbearbeitungphp
// Beschreibung: Formular zum Bearbeiten verschiedener Eigenschaften der variablen in der DB
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

//$dblink = dbopen();
$db = new SQLite3($dbpfad);

?>
<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="graphenanlegen.php">
    <meta name="Dateiversion" content="1.0">
    <meta name="Dateidatum"   content="25.03.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
  <script language="JavaScript" type="text/JavaScript">
function aenderungenuebernehmen()
{
		document.varbearb.action = 'variablebearb.php';
		document.varbearb.submit();
}

function ipsnamenzuweisen()
{
	if(confirm('Wollen Sie allen Variablen den aktuellen IPS Pfad und Namen als neuen Variablennamen zuweisen?'))
	{

			document.varbearb.action = 'variableipsnameuebern.php';
			document.varbearb.submit();

	}
}
</script>
  <body>
    <br>
    <center><span style="font-weight: bold;">SQLite DUG Tool<br>
	Variablen bearbeiten</span><br>
	<div style="text-align: right;"><a href="http:./verwaltung.php">zur Verwaltungsansicht</a> <br>
	<a href="http:./index.php">zur&uuml;ck zur Startseite</a><br></div>
      <br>
      <br>
<?
if (isset($_REQUEST['varselection']))
{
    $zusatz = "WHERE ";
	$temp = "";
	reset($_REQUEST['varselection']);
	foreach ($_REQUEST['varselection'] as $k => $v)
	{
		$zusatz = $zusatz.$temp."IPSID = '".$v."'";
		$temp = " OR ";
	}
}
else
{
	echo "Es wurde keine Variable ausgewählt. Daher werden alle Variablen angezeigt.<br><br>";
	$zusatz = "";
}
?>



	  <br>
<form name="varbearb"  method="post"  target="_self">


<span style="font-weight: bold;">Variablen&uuml;bersicht</span>
<br>
        <table class="tab_normal" witdh="100%" cellspacing="3" cellpadding="3" >
          <colgroup>
            <col width="5%">
            <col width="45%">
            <col width="10%">
            <col width="10%">
            <col width="10%">
            <col width="10%">
			<col width="10%">
          </colgroup>
          <tr class="tab_headline">
            <td>IPS ID</td>
            <td>IPS Name</td>
            <td>Einheit</td>
            <td>Typ</td>
            <td>Datens&auml;tze</td>
			<td>max. Datens&auml;tze<br> 0 = unbegrenzt</td>
			<td>&auml;lteste Datens&auml;tze<br> 0 = unbegrenzt</td>
          </tr>
<?
//wähle alle Variablen aus,, aufsteigend sortiert nach ihren IPS IDs
$select = "SELECT * FROM Variable ".$zusatz." ORDER BY Name;";
$varquery = $db->query($select);
$i =0;
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

?>
	<input name="variable[<?echo $i; ?>][0]" type="hidden" value ="<?echo $dbvarinfo['ID'];?>">
	   <tr class="tab_normal_line">
        <td><?echo $dbvarinfo['IPSID'];?><input name="variable[<?echo $i; ?>][5]" type="hidden" value ="<?echo $dbvarinfo['IPSID'];?>"></td>
		<td><input name="variable[<?echo $i; ?>][1]" type="text" size="80" maxlength="255" value ="<?echo $dbvarinfo['Name'];?>"></td>
		<td><input name="variable[<?echo $i; ?>][2]" type="text" size="10" maxlength="255" value ="<?echo ($dbvarinfo['Einheit']? $dbvarinfo['Einheit'] : 'unbekannt');?>"></td>
        <td><?echo $vartype;?></td>
        <td><?echo $varcount['Anzahl'];?></td>
		<td><input name="variable[<?echo $i; ?>][3]" type="text" size="10" maxlength="10" value ="<?echo ($dbvarinfo['MaxAnzahl'] ? $dbvarinfo['MaxAnzahl'] : '0');?>" <? echo ((int)$dbvarinfo['MaxAnzahl'] != 0? "style=\"background-color:#FFCC33;\"" : "")?>></td>
		<td><select name="variable[<?echo $i; ?>][4]" size="1">
<?
								foreach($VARIABLESPEICHERDAUER as $zeitintervall => $bezeichnung)
								{
									echo "<option value=\"".$zeitintervall."\" ".($dbvarinfo['MaxIntervallZeit'] == $zeitintervall? "selected" : "")." ".((int)$dbvarinfo['MaxIntervallZeit'] != 0? "style=\"background-color:#FFCC33;\"" : "").">".$bezeichnung."</option>";
								}
?>
							</select>
		</td>
       </tr>


<?
$i++;
}
$db->close();
?>

        </table>
		</form>
        <br>
		<span style="font-weight: bold;">Achtung: Bitte achten Sie selbst darauf, dass sich in der vorletzten Spalte NUR Ziffern befinden!</span> <br>
		<br>
	  <form>
		<table>
			<tr>
				<td><input type="button" name="neu" value="&Auml;nderungen &uuml;bernehmen" onClick="javascript:aenderungenuebernehmen();"></td>
				<td><input type="button" name="neu" value="IPS Var. Namen &uuml;bernehmen" onClick="javascript:ipsnamenzuweisen();"></td>
			</tr>
		</table>
      </form>
    </center>
  </body>
</html>

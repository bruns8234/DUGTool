<?
//==================================================================================
// Datei.......: graphenbearbeiteneinfach.php
// Beschreibung: Formular zum Bearbeiten von einem oder allen Graphen. Nimmt den Namen, die Größe, Timervariablen
//		und die Auswahl der darzustellenden Variablen entgegen
//		Bietet eine einfache Unterstützung beim Anlagen der Graphen
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
?>
<html>
  <head>
    <meta name="Paketname"    content="SQLite DUG Tool">
    <meta name="Dateiname"    content="graphenbearbeiteneinfach.php">
    <meta name="Dateiversion" content="1.5">
    <meta name="Dateidatum"   content="01.05.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>
  <body>
    <br>
    <center><span style="font-weight: bold;">SQLite DUG Tool<br>
	Graphen bearbeiten</span><br>
	<div style="text-align: right;"><a href="http:./verwaltung.php">zur Verwaltungsansicht</a> <br>
	<a href="http:./index.php">zur&uuml;ck zur Startseite</a><br></div>
      <br>

      <form name="graphbearbeiten"  method="post" action="graphbearb.php" target="_self">
<?
if (isset($_REQUEST['graphenauswahl']))
{
    $zusatz = "WHERE ";
	$temp = "";
	reset($_REQUEST['graphenauswahl']);
	foreach ($_REQUEST['graphenauswahl'] as $k => $v)
	{
		$zusatz = $zusatz.$temp."ID = '".$v."'";
		$temp = " OR ";
	}
}
else
{
	echo "Es wurde kein Graph ausgew&auml;hlt. Daher werden alle Graphen angezeigt.<br><br>";
	$zusatz = "";
}



	$select = "SELECT * FROM Graph ".$zusatz." ORDER BY ID ASC";		//alle angelegten Graphen raussuchen
	$graphquery = $db->query($select);
	$i=0;
	while($graphentry = $graphquery->fetchArray())			//alle gefundenen Graphen durchlaufen
	{

?>
	<center><span style="font-weight: bold;">Graph <? echo $graphentry['ID'] ?>: <? echo $graphentry['Name'] ?></span>
	<br>
	<br>
	<table class="tab_normal" witdh="100%" cellspacing="3" cellpadding="3" >
          <colgroup>
            <col width="25%">
            <col width="25%">
            <col width="10%">
            <col width="10%">
            <col width="15%">
			<col width="15%">
          </colgroup>
          <tr class="tab_headline">
            <td>Name / Beschreibung</td>
            <td>&Uuml;berschrift</td>
            <td>Gr&ouml;sse X in px</td>
			<td>Gr&ouml;sse Y in px</td>
            <td>Neuerstellungsintervall</td>
			<td>Darstellungsintervall</td>
          </tr>

          <tr class="tab_normal_line">
            <td align="left"><input name="gname[<? echo $i; ?>]" type="text" size="40" maxlength="255" value = "<?echo $graphentry['Name'];?>"></td>
            <td align="left"><input name="gueberschrift[<? echo $i; ?>]" type="text" size="40" maxlength="255" value = "<?echo $graphentry['Ueberschrift'];?>"></td>
            <td align="left"><input name="ggroessex[<? echo $i; ?>]" type="text" size="10" maxlength="5" value = "<?echo $graphentry['GroesseX'];?>"></td>
			<td align="left"><input name="ggroessey[<? echo $i; ?>]" type="text" size="10" maxlength="5" value = "<?echo $graphentry['GroesseY'];?>"></td>
			<td align="left"> <select name="gerstintervall[<? echo $i; ?>]" size="1">
<?
									foreach($NEUERSTELLUNGSINTERVALL as $zeitintervall => $bezeichnung)
									{
										echo "<option value=\"".$zeitintervall."\" ".($graphentry['ErstellungsIntervall'] == $zeitintervall? "selected" : "").">".$bezeichnung."</option>";
									}
?>
						     </select>
			</td>
			<td align="left"> <select name="gdarintervall[<? echo $i; ?>]" size="1">
<?
								foreach($DARSTELLUNGSINTERVALL as $zeitintervall => $bezeichnung)
								{
									echo "<option value=\"".$zeitintervall."\" ".($graphentry['DatenIntervall'] == $zeitintervall? "selected" : "").">".$bezeichnung."</option>";
								}
?>
					</select>
			</td>

          </tr>

        </table>
		<input type="hidden" name="gerstoffset[<? echo $i; ?>]" value="<? echo (int)$graphentry['ErstellungsOffset']; ?>">
		<input type="hidden" name="gID[<? echo $i; ?>]" value="<? echo $graphentry['ID']; ?>">
        <br>
		<table class="tab_normal" witdh="100%" cellspacing="3" cellpadding="3" >
          <colgroup>
            <col width="50%">
            <col width="50%">
          </colgroup>
          <tr class="tab_headline">
            <td>Graphentyp</td>
            <td>Balkenintervall</td>
          </tr>
          <tr class="tab_normal_line">
			<td>    <select name="gTyp[<? echo $i; ?>]" size="1">
<?
					foreach($GRAPHENTYP as $wert => $bezeichnung)
					{
						echo "<option value=\"".$wert."\" ".($wert == $graphentry['GraphenTyp']? "selected" : "").">".$bezeichnung."</option>";
					}
?>
				</select>
			</td>
			<td> <select name="gBarGraphIntervall[<? echo $i; ?>]" size="1">
<?
								foreach($BARGRAPHINTERVALL as $zeitintervall => $bezeichnung)
								{
									echo "<option value=\"".$zeitintervall."\" ".($zeitintervall == $graphentry['BarGraphIntervall']? "selected" : "").">".$bezeichnung."</option>";
									echo $zeitintervall."<br>";

								}
?>
					</select>
			</td>
          </tr>
        </table>
		<br>

<span style="font-weight: bold;">zugewiesene Variablen</span>
<br>
<br>


        <table class="tab_normal" witdh="100%" cellspacing="3" cellpadding="3" >
          <colgroup>
            <col width="5%">
            <col width="60%">
            <col width="10%">
            <col width="5%">
            <col width="5%">
			<col width="5%">
			<col width="10%">
          </colgroup>
          <tr class="tab_headline">
            <td>IPS ID</td>
            <td>IPS Name</td>
            <td>Einheit</td>
            <td>Typ</td>
            <td>Datens&auml;tze</td>
			<td>Farbe</td>
			<td>Liniendicke</td>
          </tr>
<?
			//alle Variablen abrufen, die in diesem Graphen dargestellt werden
			$select = "SELECT * FROM Graphenliste WHERE GraphID = '".$graphentry['ID']."' ORDER BY ZWert ASC;";
			$varquery = $db->query($select);
			while($dbvarentry = $varquery->fetchArray())
			{
					//die  Zusatzinformationen über die Variablen abfragen
					$select = "SELECT * FROM Variable WHERE ID = '".$dbvarentry['VarID']."';";
					$variablequery =$db->query($select);
					$dbvarinfo = $variablequery->fetchArray();

					 $vartype = ((int)$dbvarinfo['Typ'] == 0 ? 'Boolean' : '').
			                 ((int)$dbvarinfo['Typ'] == 1 ? 'Integer' : '').
			                 ((int)$dbvarinfo['Typ'] == 2 ? 'Float' : '').
							 ((int)$dbvarinfo['Typ'] == 3 ? 'String' : '');

					//abfragen, wie viele Datensätze dieser Variablen in der DB sind
					$select = "SELECT COUNT(ID) AS Anzahl FROM VarEreignis WHERE VarID = '".$dbvarinfo['ID']."';";
					$varcountquery =$db->query($select);
					$varcount = $varcountquery->fetchArray();

?>	   <tr class="tab_normal_line">
        <td><?echo $dbvarinfo['IPSID'];?></td>
		<td><?echo $dbvarinfo['Name'];?></td>
		<td><?echo $dbvarinfo['Einheit'];?></td>
        <td><?echo $vartype;?></td>
        <td><?echo $varcount['Anzahl'];?></td>
		<td><select name="varfarbe[<?echo $i; ?>][<?echo $dbvarentry['VarID']; ?>]" size="1">
<?					foreach($FARBKONSTANTEN as $farbwert => $farbname)
					{
						echo "<option style=\"color:#".$farbwert.";\" value=\"".$farbwert."\" ".($dbvarentry['VarFarbe'] == $farbwert ? "selected" : "").">".$farbname."</option>";
					}
?>
			</select>
		</td>
		<td>    <select name="varlinie[<?echo $i; ?>][<?echo $dbvarentry['VarID']; ?>]" size="1">
<?					foreach($LINIENKONSTANTEN as $linienwert => $bezeichnung)
					{
						echo "<option value=\"".$linienwert."\" ".($dbvarentry['LinienTyp'] == $linienwert? "selected" : "").">".$bezeichnung."</option>";
					}
?>
				</select>
		</td>
       </tr>
	   <input type="hidden" name="vardarst[<?echo $i; ?>][<?echo $dbvarentry['VarID']; ?>]" value="<?echo (int)$dbvarentry['DarGanzzahl']; ?>">
	   <input type="hidden" name="varFilter[<?echo $i; ?>][<?echo $dbvarentry['VarID']; ?>]" value="<?echo $dbvarentry['Filter']; ?>">
	   <input type="hidden" name="varZWert[<?echo $i; ?>][<?echo $dbvarentry['VarID']; ?>]" value="<?echo $dbvarentry['Zwert']; ?>">


<?
			}
?>	</table>
    <br>
	<br>
	<br>
	<br>
	<br>

<?
	$i = $i +1;
	}

$db->close();
?>



        <input type="submit" name="bearb" value="&Auml;nderungen &uuml;bernehmen">
      </form>
    </center>
  </body>
</html>

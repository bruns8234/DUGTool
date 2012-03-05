<?
//==================================================================================
// Datei.......: graphenanlegen.php
// Beschreibung: Formular zum anlegen eines neuen Graphen. Nimmt den Namen, die Größe, Timervariablen
//		und die auswahl der darzustellenden Variablen entgegen
//		Bietet die Möglichkeit alle Einstellungen des neuen Graphen beliebig anzupassen
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
  <body>
    <br>
    <center><span style="font-weight: bold;">SQLite DUG Tool<br>
	Einen neuen Graphen anlegen</span><br>
	<div style="text-align: right;"><a href="http:./verwaltung.php">zur Verwaltungsansicht</a> <br>
	<a href="http:./index.php">zur&uuml;ck zur Startseite</a><br></div>

      <br>
      <br>
	  <b>Notwendige Angaben</b>
      <br>

	  <br>
      <form name="graphneu"  method="post" action="graphneu.php" target="_self">
        <table class="tab_normal" witdh="100%" cellspacing="3" cellpadding="3" >
          <colgroup>
            <col width="30%">
            <col width="30%">
            <col width="5%">
            <col width="5%">
            <col width="10%">
			<col width="10%">
			<col width="10%">
          </colgroup>
          <tr class="tab_headline">
            <td>Name / Beschreibung</td>
            <td>&Uuml;berschrift</td>
            <td>Gr&ouml;sse X in px</td>
			<td>Gr&ouml;sse Y in px</td>
            <td>Neuerstellungs-<br>intervall in s</td>
			<td>Darstellungs-<br>intervall in s</td>
			<td>Erstellungs-<br>offset in s</td>
          </tr>
          <tr class="tab_normal_line">
            <td align="left"><input name="gname" type="text" size="40" maxlength="255" value = "<?echo $GRAPHENNAME;?>"></td>
            <td align="left"><input name="gueberschrift" type="text" size="40" maxlength="255" value = "<?echo $GRAPHENUEBERSCHRIFT;?>"></td>
            <td align="left"><input name="ggroessex" type="text" size="10" maxlength="5" value = "<?echo $GRAPHGROESSEX;?>"></td>
			<td align="left"><input name="ggroessey" type="text" size="10" maxlength="5" value = "<?echo $GRAPHGROESSEY;?>"></td>
            <td align="left"><input name="gerstintervall" type="text" size="10" maxlength="9" value = "<?echo $NEUERSTELLUNGSINTERVALLSTD;?>"></td>
			<td align="left"><input name="gdarintervall" type="text" size="10" maxlength="9" value = "<?echo $DARSTELLUNGSINTERVALLSTD;?>"></td>
			<td align="left"><input name="gerstoffset" type="text" size="10" maxlength="9" value = "<?echo $ERSTELLUNGSOFFSETSTD;?>"></td>
          </tr>

        </table>
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
			<td>    <select name="gTyp" size="1">
<?
					foreach($GRAPHENTYP as $wert => $bezeichnung)
					{
						echo "<option value=\"".$wert."\" ".($wert == GRAPHEN_TYP_STD? "selected" : "").">".$bezeichnung."</option>";
					}
?>
				</select>
			</td>
			<td> <select name="gBarGraphIntervall" size="1">
<?
								foreach($BARGRAPHINTERVALL as $zeitintervall => $bezeichnung)
								{
									echo "<option value=\"".$zeitintervall."\" ".($zeitintervall == BARGRAPH_INTERVALL_STD? "selected" : "").">".$bezeichnung."</option>";
								}
?>
					</select>
			</td>
          </tr>

        </table>

<br>
<b>Variablenauswahl</b>
<br>
(Es werden nur Variablen angezeigt, die mindestens EINEN Eintrag in der DB haben)
<br>
(Variablen vom Typ "String" werden nicht angezeigt!)
<br>


        <table class="tab_normal" witdh="100%" cellspacing="3" cellpadding="3" >
          <colgroup>
            <col width="5%">
            <col width="5%">
            <col width="50%">
            <col width="10%">
            <col width="5%">
            <col width="5%">
			<col width="5%">
			<col width="10%">
			<col width="5%">
          </colgroup>
          <tr class="tab_headline">
            <td>Markieren</td>
            <td>IPS ID</td>
            <td>IPS Name</td>
            <td>Einheit</td>
            <td>Typ</td>
            <td>Datens&auml;tze</td>
			<td>Farbe</td>
			<td>Liniendicke</td>
			<td>Ganzzahldarst.</td>
          </tr>
<?
//wähle alle Variablen aus, die nicht vom Typ String sind, aufsteigend sortiert nach ihren IPS IDs
$select = "SELECT * FROM Variable WHERE Typ != 3 ORDER BY Name ASC;";
$varquery =$db->query($select);
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

?>	   <tr class="tab_normal_line">
		<td><center><input type="checkbox" name="varselection[<?echo $i; ?>]" value="<?echo $dbvarinfo['ID'];?>"><center></td>
        <td><?echo $dbvarinfo['IPSID'];?></td>
		<td><?echo $dbvarinfo['Name'];?></td>
		<td><?echo $dbvarinfo['Einheit'];?></td>
        <td><?echo $vartype;?></td>
        <td><?echo $varcount['Anzahl'];?></td>
		<td>    <select name="varfarbe[<?echo $i; ?>]" size="1">
<?
					foreach($FARBKONSTANTEN as $farbwert => $farbname)
					{
						echo "<option style=\"color:#".$farbwert.";\" value=\"".$farbwert."\">".$farbname."</option>";
					}
?>
				</select>
		</td>
		<td>    <select name="varlinie[<?echo $i; ?>]" size="1">
<?					foreach($LINIENKONSTANTEN as $linienwert => $bezeichnung)
					{
						echo "<option value=\"".$linienwert."\" ".($linienwert == $LINIENKONSTANTENSTD? "selected" : "").">".$bezeichnung."</option>";
					}
?>
				</select>
		</td>
		<td><center><input type="checkbox" name="vardarst[<?echo $i; ?>]" <?echo ((int)$dbvarinfo['Typ'] == 2 ? '' : 'checked');?>><center></td>
       </tr>


<?
$i++;
}
$db->close();
?>

        </table>
        <br>
        <input type="submit" name="neu" value="Graphen jetzt anlegen">
      </form>
    </center>
  </body>
</html>

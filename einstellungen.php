<?
//==================================================================================
// Datei.......: einstellungen.php
// Beschreibung: Seite um die Einstellungen im DUG Tool zu verändern
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
    <meta name="Dateiname"    content="einstellungen.php">
    <meta name="Dateiversion" content="1.0">
    <meta name="Dateidatum"   content="09.04.2009">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" type="text/css" href="./mystyle.css">
  </head>

  <body>
    <br>
    <center><span style="font-weight: bold;">SQLite DUG Tool<br>
		Einstellungen</span><br>
		<div style="text-align: right;"><a href="http:./index.php">zur&uuml;ck zur Startseite</a><br></div>

	  <br>
      <br>
      <br>

      <form name="optionen"  method="post" action="einstuebern.php" target="_self">
        <table class="tab_normal" witdh="100%" cellspacing="3" cellpadding="3" >
          <colgroup>
            <col width="50%">
            <col width="50%">

          </colgroup>
          <tr class="tab_headline">
            <td>Beschreibung</td>
            <td>Wert</td>

          </tr>
<?
//alle Einstellungen aus der Datenbank abrufen
$select = "SELECT * FROM Zusatz";
$query = $db->query($select);
while($zusatzeintrag = $query->fetchArray())
{

	if ($zusatzeintrag['Bezeichnung'] == 'updateScriptID')
	{
		$zeile1 = "<tr class=\"tab_normal_line\">
            <td>Die ID des DBupdate.php Scriptes in IPS.<br>Bitte ver&auml;ndern sie diese Zahl nur, wenn sie absolut sicher sind!</td>
            <td><input name=\"DBupdateID\" size=\"60\" maxlength=\"5\" value=\"".(int)$zusatzeintrag['Wert']."\"></td>
          </tr>";
	}
	if ($zusatzeintrag['Bezeichnung'] == 'GraphenupdateID')
	{
		$zeile2 = "<tr class=\"tab_normal_line\">
            <td>Die ID des Graphupdate.php Scriptes in IPS.<br>Bitte ver&auml;ndern sie diese Zahl nur, wenn sie absolut sicher sind!</td>
            <td><input name=\"GraphupdateID\" size=\"60\" maxlength=\"5\" value=\"".(int)$zusatzeintrag['Wert']."\"></td>
          </tr>";
	}
    if ($zusatzeintrag['Bezeichnung'] == 'Graphenpfad')
	{
		$zeile3 = "<tr class=\"tab_normal_line\">
            <td>Pfad zum Speicherort der Graphendiagramme<br>Der Pfad muss immer mit einem \\ (Backslash) enden!</td>
            <td>".dirname(__FILE__)."\<br><input name=\"Graphenpfad\" size=\"60\"  value=\"".$zusatzeintrag['Wert']."\"></td>
          </tr>";
	}

    if ($zusatzeintrag['Bezeichnung'] == 'erwGraphenpfad')
	{
		$zeile4 = "<tr class=\"tab_normal_line\">
            <td>Gesammtpfad zum Speicherort der Graphendiagramme<br>Der Pfad muss immer mit einem \\ (Backslash) enden!</td>
            <td><input name=\"erwGraphenpfad\" size=\"60\"  value=\"".str_replace(chr(92), chr(92) . chr(92), $zusatzeintrag['Wert'])."\"></td>
          </tr>";
	}
    if ($zusatzeintrag['Bezeichnung'] == 'registriereMedia')
	{
		$zeile5 = "<tr class=\"tab_normal_line\">
            <td>Sollen die Graphendiagramme in IPS als neue Medien eingebunden werden?</td>
            <td><input type=\"checkbox\" name=\"registriereMedia\" ".((int)$zusatzeintrag['Wert'] == 1 ? 'checked' : '')."></td>
          </tr>";
	}
    if ($zusatzeintrag['Bezeichnung'] == 'dbversion')
	{
		$zeile6 = "<tr class=\"tab_normal_line\">
            <td>Sie benutzen die DUG Tool Version</td>
            <td>".(int)$zusatzeintrag['Wert']."</td>
          </tr>";
	}
	if ($zusatzeintrag['Bezeichnung'] == 'Erstellungsdatum')
	{
		$zeile7 = "<tr class=\"tab_normal_line\">
            <td>Diese Datenbank wurde erstellt am:</td>
            <td>".date("d.m.Y",$zusatzeintrag['Wert'])." um ".date("H:i",$zusatzeintrag['Wert'])." Uhr</td>
          </tr>";
	}
	if ($zusatzeintrag['Bezeichnung'] == 'erlaubeY2Achse')
	{
		$zeile8 = "<tr class=\"tab_normal_line\">
            <td>Sollen bei unterschiedlichen Variableneinheiten zwei Y-Achsen angezeigt werden?</td>
            <td><input type=\"checkbox\" name=\"erlaubeY2Achse\" ".((int)$zusatzeintrag['Wert'] == 1 ? 'checked' : '')."></td>
          </tr>";
	}
	if ($zusatzeintrag['Bezeichnung'] == 'DUGKategorieID')
	{
		$zeile9 = "<tr class=\"tab_normal_line\">
            <td>Die IPS ID der Kategorie des DUG Tools .<br>Bitte ver&auml;ndern sie diese Zahl nur, wenn sie absolut sicher sind!</td>
            <td><input name=\"DUGKategorieID\" size=\"60\" maxlength=\"5\" value=\"".(int)$zusatzeintrag['Wert']."\"></td>
          </tr>";
	}
	if ($zusatzeintrag['Bezeichnung'] == 'DUGMediaKategorieID')
	{
		$zeile10 = "<tr class=\"tab_normal_line\">
            <td>Die IPS ID der Kategorie in der die Graphendiagramme abgelegt werden.<br>Bitte ver&auml;ndern sie diese Zahl nur, wenn sie absolut sicher sind!</td>
            <td><input name=\"DUGMediaKategorieID\" size=\"60\" maxlength=\"5\" value=\"".(int)$zusatzeintrag['Wert']."\"></td>
          </tr>";
	}
	if ($zusatzeintrag['Bezeichnung'] == 'GraphenKonfiguration')
	{
		$zeile11 = "<tr class=\"tab_normal_line\">
            <td>Konfigurationsdatei, die das Aussehen des Graphen bestimmt.<br> Die Grundeinstellungen befinden sich in der 'GraphenKonfiguration_std.php'.</td>
            <td><input name=\"GraphenKonfiguration\" size=\"60\"  value=\"".$zusatzeintrag['Wert']."\"></td>
          </tr>";
	}
}
echo "<big>".$zeile6.$zeile7.$zeile1.$zeile2.$zeile9.$zeile10.$zeile3.$zeile4.$zeile11.$zeile5.$zeile8."</big>";
?>
        </table>
        <br>
		<input type="submit" name="bearb" value="&Auml;nderungen &uuml;bernehmen">
		</form>
<?
$db->close();
?>
    </center>
  </body>
</html>

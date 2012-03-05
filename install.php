<?
//==================================================================================
// Datei.......: install.php
// Beschreibung: Prüft die Systemvoraussetzungen und nimmt einige Konfigurationen entgegen
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 81 $
// zuletzt geändert : 	$Date: 2009-09-27 19:35:27 +0200 (So, 27 Sep 2009) $
// Author:			$Author: tobias $
//
// 23.10.2011 TGUSI74
// + SQLite3 Anpassungen
//==================================================================================

function phpinfo_array(){
 ob_start();
 phpinfo(-1);

 $pi = preg_replace(
 array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
 '#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
 "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
  '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
  .'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
  '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
  '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
  "# +#", '#<tr>#', '#</tr>#'),
 array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
  '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
  "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
  '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
  '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
  '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
 ob_get_clean());

 $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
 unset($sections[0]);

 $pi = array();
 foreach($sections as $section)
 {
   $n = substr($section, 0, strpos($section, '</h2>'));
   preg_match_all(
   '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
     $section, $askapache, PREG_SET_ORDER);
   foreach($askapache as $m)
	{
		if (!isset($m[3]) || $m[2]==$m[3])
		{
			if(isset($m[2]))
			{
				$pi[$n][$m[1]]=$m[2];
			}
		}
		else
		{
			$pi[$n][$m[1]]=array_slice($m,2);
		}

	}
 }

 return $pi;
}

$extensions = get_loaded_extensions();
$infos      = phpinfo_array();
$ext_IPS    = (array_search("IP-Symcon Exported Functions", $extensions) !== FALSE ? TRUE : FALSE);
$ext_gd     = (array_search("gd", $extensions) !== FALSE ? TRUE : FALSE);
$ext_sqlite  = (array_search("SQLite", $extensions) !== FALSE ? TRUE : FALSE);
$ext_pdo = (array_search("PDO", $extensions) !== FALSE ? TRUE : FALSE);
$php_version = $infos["PHP Configuration"]["PHP Version"];
$php_ServerAPI = $infos["PHP Configuration"]["Server API"];
$php_IniPath = $infos["PHP Configuration"]["Configuration File (php.ini) Path"];
$php_extDir = $infos["PHP Core"]["extension_dir"];
$php_maxExecTime = $infos["PHP Core"]["max_execution_time"];
if ($ext_gd)
{
  $gd_version = $infos["gd"]["GD Version"];
}
else
{
	$gd_version = 'nicht vorhanden';
}
if ($ext_sqlite) {
  $sqlite_version = $infos["SQLite"]["SQLite Library"];
}
else
{
	$sqlite_version = 'nicht vorhanden';
}
if ($ext_IPS) {
  $ipsversion = IPS_GetKernelVersion();
} else
{
	$ipsversion = 0;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

  <meta name="Paketname" content="SQLite DUG Tool">
  <meta name="Dateiname" content="install.php">
  <meta name="Dateiversion" content="1.0">
  <meta name="Dateidatum" content="28.03.2009">
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <link rel="stylesheet" type="text/css" href="./mystyle.css">
<script language="JavaScript" type="text/JavaScript">
function installationstarten()
{

	if (document.info.voraussetzung.value == "TRUE")
	{
		if ((document.dbdaten.nocomm.checked == true) && (document.dbdaten.noresp.checked == true))
		{
			document.dbdaten.action = 'doinstall.php';

			document.dbdaten.submit();
		}
		else
		{
			alert("Sie müssen zur Installation die angegebenen Bedingungen akzeptieren.");
		}

	}
	else
	{
		alert("Es sind nicht alle Vorausetzungen für die Installation erfüllt. Bitte lesen Sie die Zusammenfassung!");
	}
}
  </script>
</head>


<body>

<br>

<center><span style="font-weight: bold;">Installationsscript
zum SQLite Datenbank Und Graphen (DUG) Tool</span><br>

<br>

<br>

<span style="font-weight: bold;">Vorraussetzungen</span><b><br>

</b>
<table class="tab_normal" witdh="100%" cellpadding="3" cellspacing="3">

  <colgroup><col width="50%"><col width="50%"></colgroup>
  <tbody>

    <tr class="tab_headline">

      <td>Komponente</td>

      <td>Status</td>

    </tr>

    <tr class="tab_normal_line">

      <td>IP-Symcon Framework</td>

      <td><? echo ($ext_IPS ? "Vorhanden" : "Nicht vorhanden")."\r\n";?></td>

    </tr>

      <td>IP-Symcon Kernelversion</td>

      <td><? echo $ipsversion."\r\n";?></td>

    </tr>

    <tr>

      <td>PHP Version</td>

      <td><? echo $php_version;?></td>

    </tr>

    <tr>

      <td>PHP Server-API</td>

      <td><? echo $php_ServerAPI;?></td>

    </tr>

    <tr>

      <td>Pfad zur genutzten php.ini</td>

      <td><? echo $php_IniPath;?></td>

    </tr>

    <tr>

      <td>Verzeichnis der Erweiterungen</td>

      <td><? echo $php_extDir;?></td>

    </tr>

    <tr>

      <td>Maximale Script-Laufzeit</td>

      <td><? echo $php_maxExecTime;?></td>

    </tr>

    <tr>

      <td>GD Unterstützung (zur Bilderzeugung)</td>

      <td><? echo ($ext_gd ? "Vorhanden" : "Nicht vorhanden")."\r\n";?></td>

    </tr>

    <tr>

      <td>GD Version</td>

      <td><? echo $gd_version;?></td>

    </tr>

	<tr>

      <td>PDO Unterstützung (DB Treiber)</td>

      <td><? echo ($ext_pdo ? "Vorhanden" : "Nicht vorhanden")."\r\n";?></td>

    </tr>


    <tr>

      <td>SQLite Unterstützung (DB Treiber)</td>

      <td><? echo ($ext_sqlite ? "Vorhanden" : "Nicht vorhanden")."\r\n";?></td>

    </tr>

    <tr>

      <td>SQLite Version</td>

      <td><? echo $sqlite_version;?></td>

    </tr>

  </tbody>
</table>

<br>

<br>
<form name="info">



<span style="font-weight: bold;">Zusammenfassung:<br>
</span>
<br>
<?

if (!$ext_IPS) {
  echo "<span style=\"font-weight: bold;\"> Dieses ist KEINE gültige IPS-Umgebung! Sie müssen das SQLite DUG Tool auf einem IPS-Webserver installieren!</span>
        <input type=\"hidden\" name=\"voraussetzung\" value=\"FALSE\">";
}
else if (substr($ipsversion,0,1) == '1') {
  echo "<span style=\"font-weight: bold;\"> Sie benutzen die IPS Version 1. Dieses Tool ist für IPS V2 geschrieben worden und ist unter Version 1 NICHT lauffähig!</span>
        <input type=\"hidden\" name=\"voraussetzung\" value=\"FALSE\">";
}
else
{
  if ($ext_gd AND $ext_sqlite) {
    echo " <span style=\"font-weight: bold;\"> Alle Installationsvoraussetzungen sind erf&uuml;llt. Sie k&ouml;nnen jetzt fortfahren und das SQLite DUG Tool installieren.</span>
	<input type=\"hidden\" name=\"voraussetzung\" value=\"TRUE\"><br>";
  }
  else
  {
    echo "<input type=\"hidden\" name=\"voraussetzung\" value=\"FALSE\">";
    if (!$ext_gd) {
      echo "<span style=\"font-weight: bold;\">  - Die GD-Unterst&uuml;tzung ist nicht installiert. Bitte installieren Sie die Datei \"php_gd2.dll\""."</span><br>";
	  echo "Anleitung: Laden Sie das PHP Paket <a href=\"http://museum.php.net/php5/php-".$php_version."-Win32.zip\">http://museum.php.net/php5/php-".$php_version."-Win32.zip</a>
	  herunter und entpacken Sie die Datei php_gd2.dll aus dem Unterverzeichnis \ext\ des heruntergeladenen Archives in das Verzeichnis \" ".$php_extDir." \".
	  Dann f&uuml;gen Sie die Zeile \"extension=php_gd2.dll\" in Ihrer php.ini,  unter ".$php_IniPath." hinzu. Abschließend f&uuml;hren Sie einen Neustart von IPSymcon
	  durch und starten die Installation des SQLite DUG Tools erneut.<br>" ;
    }
	if (!$ext_pdo) {
      echo "<span style=\"font-weight: bold;\">  - Die PDO-Unterst&uuml;tzung ist nicht installiert. Bitte installieren Sie die Datei \"php_pdo.dll\""."</span><br>";
	  echo "Anleitung: Laden Sie das PHP Paket <a href=\"http://museum.php.net/php5/php-".$php_version."-Win32.zip\">http://museum.php.net/php5/php-".$php_version."-Win32.zip</a>
	  herunter und entpacken Sie die Datei php_pdo.dll aus dem Unterverzeichnis \ext\ des heruntergeladenen Archives in das Verzeichnis \" ".$php_extDir." \".
	  Dann f&uuml;gen Sie die Zeile \"extension=php_pdo.dll\" in Ihrer php.ini, unter ".$php_IniPath." hinzu. Abschließend f&uuml;hren Sie einen Neustart von IPSymcon
	  durch und starten die Installation des SQLite DUG Tools erneut.<br>" ;
    }
    if (!$ext_sqlite3) {
      echo "<span style=\"font-weight: bold;\">  - Die SQLite-Unterst&uuml;tzung ist nicht installiert. Bitte installieren Sie die Datei \"php_sqlite3.dll\""."</span><br>";
	  echo "Anleitung: Laden Sie das PHP Paket <a href=\"http://museum.php.net/php5/php-".$php_version."-Win32.zip\">http://museum.php.net/php5/php-".$php_version."-Win32.zip</a>
	  herunter und entpacken Sie die Datei php_sqlite.dll aus dem Unterverzeichnis \ext\ des heruntergeladenen Archives in das Verzeichnis \" ".$php_extDir." \".
	  Dann f&uuml;gen Sie die Zeile \"extension=php_sqlite3.dll\" in Ihrer php.ini, unter ".$php_IniPath." hinzu. Abschließend f&uuml;hren Sie einen Neustart von IPSymcon
	  durch und starten die Installation des SQLite DUG Tools erneut.<br>" ;
    }
  }

  if ($php_maxExecTime < 30) {

    echo "<span style=\"font-weight: bold;\"> Zus&auml;tzlicher Hinweis:</span>  Die maximale Ausf&uuml;hrungszeit für PHP-Skripte ist auf ".$php_maxExecTime." Sekunden
	eingestellt. Das k&ouml;nnte beim Erstellen mehrerer Graphen zu Timeout-Abbr&uuml;chen führen. Bitte setzen Sie diesen Wert in Ihrer php.ini unter ".$php_IniPath."
	auf mindestens 30 Sekunden hoch.<br>";
  }
}
?>
</form>
<br>

<br>

<br>

<br>
<span style="font-weight: bold;">
Installationskonfiguration:<br>
<br>
</span>
<form name="dbdaten" method="post" action="doinstall.php" target="_self">
	<table class="tab_normal" witdh="100%" cellpadding="3" cellspacing="3">
    <colgroup>
		<col width="50%">
		<col width="50%">
	</colgroup>
	<tbody>

    <tr class="tab_headline">
      <td><big>Beschreibung</big></td>
      <td><big>Wert</big></td>
    </tr>

    <tr class="tab_normal_line">
      <td><big>Bitte einen Dateinamen f&uuml;r die Datenbank eingeben:</big></td>
      <td><span><big><?echo IPS_GetKernelDir()."<br>";?></big><br><input name="dbdateiname" size="60" maxlength="255" value="meineIPSDatenbank.db"></span>
	  </td>
    </tr>

	<tr class="tab_normal_line">
      <td><big>Bitte den Pfad angeben, in dem die Graphen abgelegt werden sollen:</big></td>
      <td><span><big><?echo dirname(__FILE__)."\<br>";?></big><br><input name="graphenpfad" size="60" maxlength="255" value="Diagramme\"></span>
	  </td>
    </tr>

		<tr class="tab_normal_line">
      <td><big>Hiermit best&auml;tige ich, dass ich das SQLite DUG Tool und die darin verwendete <a href="http://www.aditus.nu/jpgraph/">JpGraph Bibliothek</a> nur für nicht kommerzielle Zwecke benutzen werde:</big></td>
      <td><span><big>Ich akzeptiere:</big><br><input type="checkbox" name="nocomm" value="true"></span>
	  </td>
    </tr>

		<tr class="tab_normal_line">
      <td><big>Hiermit akzeptiere ich, dass der Autor des SQLite DUG Tools keinerlei Verantwortung für eventuelle Sch&auml;den an Software und / oder Hardware &uuml;bernimmt, die durch die Benutzung dieser Software auftreten k&ouml;nnten:</big></td>
      <td><span><big>Ich akzeptiere:</big><br><input type="checkbox" name="noresp" value="true"></span>
	  </td>
    </tr>
	</tbody>
	</table>
</center>
  Wenn Sie nun auf "jetzt installieren" dr&uuml;cken, werden folgende Aktionen durchgef&uuml;hrt: <br>
  <ol>
    <li>Die Datenbank wird angelegt.</li>
	<li>Ein Script Names DBupdate wird in IPSymcon angelegt.</li>
	<li>Ein Script Names Graphupdate wird in IPSymcon angelegt.</li>
	<li>Das neue Script Graphupdate wird mit einem Timer verbunden, der es jede Minute ein Mal aufruft.</li>
	<li>Die Dateien DBupdate.php, Graphupdate.php und DUGToolbasis.php werden in Ihr IPS Scripverzeichnis kopiert.</li>
	<li>Das von Ihnen ausgewählte Verzeichnis zum Speichern der Graphendiagramme wird angelegt.</li>

  </ol>
  Wenn Sie Damit einverstanden sind, starten Sie jetzt die Installation. <br>
<center>

  <input type="button" name="install" value="jetzt installieren"  onClick="javascript:installationstarten()"><br>

</form>

</center>

</body>
</html>

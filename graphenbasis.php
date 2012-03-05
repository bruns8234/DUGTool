<?
//==================================================================================
// Datei.......: graphenbasis.php
// Beschreibung:    erstellt den Graphen desssen ID in der Funktion übergeben worden ist.
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 81 $
// zuletzt geändert : 	$Date: 2009-09-27 19:35:27 +0200 (So, 27 Sep 2009) $
// Author:			$Author: tobias $
//
// 23.10.2011 TGUSI74
//  + SQLite3 Anpassungen
// 26.10.2011 TGUSI74
//  + Fehlerbehandlung bei DB oeffnen
//==================================================================================

require_once("sqlitebasis.php");
require_once("filter.php");

require_once($jpgraphpfad."\jpgraph.php");
require_once($jpgraphpfad."\jpgraph_line.php");
require_once($jpgraphpfad."\jpgraph_date.php");
require_once($jpgraphpfad."\jpgraph_bar.php");
require_once($jpgraphpfad."\jpgraph_regstat.php");
//require_once($jpgraphpfad."\jpgraph_plotline.php");


//läd die Konfigurationsdatei für das Aussehen des Graphen
$db = new SQLite3($dbpfad, SQLITE3_OPEN_READONLY);
    if ($db === FALSE)
       {
        IPS_LogMessage("DUG GRAPHENBASIS ERROR ", "Fehler beim oeffnen der DB (eventuell altes DB-Format??? --> nach SQLITE3 migrieren)");
        return;
       }

$db->busyTimeout(10000);
$result = $db->query("SELECT Wert FROM Zusatz WHERE Bezeichnung = 'GraphenKonfiguration';");
$einstellungen = $result->fetchArray();
require_once($einstellungen['Wert']);
$db->close();


class CDatenpunkt
{
	public $x;
	public $y;
	function CDatenpunkt($xWert, $yWert)
	{
		$this->x = $xWert;
		$this->y = $yWert;
	}
}

class CBeschriftung
{
	var $timeOffset =0;
	var $timeIntervall =0;
	function CBeschriftung($offset, $intervall)
	{
		$this->timeOffset = $offset;
		$this->timeIntervall = $intervall;
	}
   function erstelleBeschriftung($aVal)
   {
	   $mytime = date("H:i",(time()-$this->timeOffset+$aVal));
	   $mydate = date("d.m.",(time()-$this->timeOffset+$aVal));
	   return $mytime." \r\n".$mydate;
   }

   function erstelleBarBeschriftung($aVal)
   {
	if(($this->timeIntervall == 2678400) && BALKEN_XACHSENBESCHRIFTUNG_MONATSNAMEN_ANZEIGEN)
	{
	   $mydate = date("M",(time()-$this->timeOffset+($this->timeIntervall*$aVal)));
	   $mydate2 = date("Y",(time()-$this->timeOffset+($this->timeIntervall*$aVal)));
	   return $mydate." \r\n".$mydate2;
	} elseif(($this->timeIntervall == 86400) && BALKEN_XACHSENBESCHRIFTUNG_WOCHENNAMEN_ANZEIGEN)
	{
		$mydate = date("D.",(time()-$this->timeOffset+($this->timeIntervall*$aVal)));
		$mydate2 = date("d.m.",(time()-$this->timeOffset+($this->timeIntervall*$aVal)));
		return $mydate." \r\n".$mydate2;

	} else
	{
		$mytime = date("H:i",(time()-$this->timeOffset+($this->timeIntervall*$aVal)));
		$mydate = date("d.m.",(time()-$this->timeOffset+($this->timeIntervall*$aVal)));
		return $mytime." \r\n".$mydate;
	}
   }
}

// Funktion exist_media aus Torro´s WIIPS angepasst von wgreipl
//weiter modifiziert und teils ausgelagert in das Installationsscript doinstall.php von TS17
function exist_media ( $pfad, $graphdateiname, $DUG_Media_ID, $ueberschrift, $name )
{
	$ParentID = 0;
	//entfernt den IPS Pfad -> absoluter Pfad wird zu relativem Pfad
	$pfad = str_replace(strtolower(IPS_GetKernelDir()), "", strtolower($pfad));

	$medien_id = @IPS_GetMediaIDBYFile($pfad.$graphdateiname);
	if (  $medien_id <> 0 ) {
		IPS_SendMediaEvent ( $medien_id );
		return true;
	}
	// Mediendatei nicht vorhanden, deshalb neu anlegen
	$medien_id = IPS_CreateMedia ( 1 );
	IPS_SetMediaFile( $medien_id, $pfad . $graphdateiname, FALSE );
	IPS_SetName( $medien_id, $name." (".$ueberschrift.")");
	IPS_SetParent($medien_id, (int)$DUG_Media_ID);
	return true;
 }
// Function exist_media ENDE

function BerechneBarTicks(&$mygraph, $xgroesse, $yPosition, $datenIntervall, $balkenIntervall)
{

	//wenn das Bargraph Intervall größer als das DatenIntervall ist, soll nur ein Balken über  das gesamte DatenIntervall angezeigt werden
	if ($datenIntervall < $balkenIntervall)
	{
		$balkenanzahl = 1;
	}
	else
	{
		$balkenanzahl = round($datenIntervall/$balkenIntervall);
	}
	//Bei Balkengraphen ergibt sich das Darstellungsintervall aus der gerundeten Balkenanzahl mal dem Balkenintervall.
	//damit soll verhindert werden,  dass bei bestimmten Darstellungs- und BalkenIntervallen, z.b. 6 Monate mit 14 Tagen Intervall
	//der erste Balken nur "halb" mit Daten gefüllt ist,  weil 6 Monate nicht ganzzahlig durch 14 Tage teilbar ist
	$meineBeschriftung = new CBeschriftung($balkenanzahl * $balkenIntervall, $balkenIntervall );
	$tickslabelarray = array();
	$abstand = $xgroesse / $balkenanzahl;
	$schritte = ceil(MINDESTABSTAND_ZWISCHEN_TICKS/$abstand);
	//echo "Balkenanzahl: ".$balkenanzahl."<br>";
	//echo "Abstand: ".$abstand."<br>";
	//echo "Schritte: ".$schritte."<br>";


	$i = 0;
	for($i; $i <= $balkenanzahl - 1;$i++)
	{
		if (($i % $schritte) == 0)
		{
			if (BALKEN_XACHSENBESCHRIFTUNG_MITTIG)
			{
				//Standardbeschriftung mittig vom Balken auf der X Achse
				//Beschriftung wird hier erstellt und im Array gespeichert. Das Array wird an JpGraph
				//übergeben und die Beschriftung automatisch positioniert
				$tickslabelarray[$i] = $meineBeschriftung->erstelleBarBeschriftung($i);
			}
			else
			{
				//selbstständig erzeugte XAchsen Beschriftung
				//Das Array zum automatischen Positionieren des BEschriftung wird leer gelassen
				//und die Beschritung manuell positioniert
				//Idee: Tick links vom Balken = Zeitpunkt, an dem das Balkenintervall startet, Tick rechts vom Balken = Zeitpunkt an dem es endet
				$tickLabel=new Text($meineBeschriftung->erstelleBarBeschriftung($i));
				$tickLabel->SetPos((int)(GRAPH_LINKER_RAND-($tickLabel->GetWidth($mygraph->img)/2)+($i*$abstand)+8), $yPosition);
				$tickLabel->SetFont(FF_ARIAL, FS_NORMAL,8);
				$tickLabel->SetColor(FARBE_X_ACHSE);
				$mygraph->AddText($tickLabel);
				$tickslabelarray[$i] = '';
			}
		}
		else
		{
			$tickslabelarray[$i] = '';
		}

	}

	//Bei Manueller Positionierung muss evtl noch ein Label ans Ende der X Achse gesetzt werden. Dies passiert hier
	if (!BALKEN_XACHSENBESCHRIFTUNG_MITTIG && (($i % $schritte) == 0))
	{
		$tickLabel=new Text($meineBeschriftung->erstelleBarBeschriftung($i));
		$tickLabel->SetPos((int)(GRAPH_LINKER_RAND-($tickLabel->GetWidth($mygraph->img)/2)+($i*$abstand)+8), $yPosition);
		$tickLabel->SetFont(FF_ARIAL, FS_NORMAL,8);
		$tickLabel->SetColor(FARBE_X_ACHSE);
		$mygraph->AddText($tickLabel);
	}
	$tickslabelarray[$i] = '';

	return $tickslabelarray;
}



function BerechneTicks($xgroesse, $intervall)
{

	//Berechne die Anzahl der Möglichen Ticks, unter der Annahme das zwischen Ihnen 80 Pixel platz sein soll
	$anzahl = ($xgroesse / ABSTAND_ZWISCHEN_HAUPTTICKS);
    //Berechne wie groß der Zeitabstand zwischen den Ticks sein soll
	$abstand = (int)($intervall / $anzahl);

	//bestimme den nächstliegenden "geraden" Zeitwert für die TBeschriftung der X Achse
	global $GRAPHXACHSENTICKS; //Array in dem die Zeitschritte gespeichert sind
	$letztergrenzwert = 0;
	foreach($GRAPHXACHSENTICKS as $wert => $grenzwert)
	{
		if (($abstand <= $grenzwert) && ($abstand >= $letztergrenzwert+1))
		{
			$abstand = $wert;
			break;
		}
		$letztergrenzwert = $grenzwert;
	}

	//Bestimme die Zeitdifferenz vom Anfang der Skala bis zum ersten Tick
	//damit wird die Zeitachse so verschoben, dass sie immer "gerade" Zeitwerte anzeigt
	// das +7200 ist dafür um den Nullpunkt auf 0 Uhr nachts zu setzen/ verschieben
	if (BESCHRIFTUNG_STARTET_BEI_NULL)
	{
		$zeitdiff = 0;
	} else
	{
		$zeitdiff = (time()+XACHSEN_OFFSET)%$abstand;
	}

	//erstelle das Array mit den einzelnen Ticks
	for ($i=0;((($i) * $abstand)-$zeitdiff)<=$intervall;$i++)
	{
		$hauptticks[$i] =  (($i) * $abstand)-$zeitdiff;
		$zwischenticks[$i] =  (($i) * $abstand)-$zeitdiff-(int)($abstand/2);
		$zwischenticks[$i+1] =  (($i+1) * $abstand)-$zeitdiff-(int)($abstand/2);
	}
	$ticksarray[0] = $hauptticks;
	$ticksarray[1] = NULL;
	//Zwischenticks sollen erste am einer Haupttickbreite von über 50 angezeigt werden. sonst sieht das Diagramm zu überladen aus
	if (($xgroesse/$intervall)*$abstand > MINDESTABSTAND_ZWISCHEN_TICKS)
	{
		$ticksarray[1] = $zwischenticks;
	}

	return $ticksarray;
}

function Graphenerstellen($ID)
{
	global $dbpfad;

	$myDummyPlotMark = new PlotMark(); //für die Legende -> damit keine Farbmarkierung vor dem Text erscheint
	$meineLegende = new Legend();
	$physEinheitY = "";
	$physEinheitY2 = "";
	$debug = false;

    $db = new SQLite3($dbpfad, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(10000);

	//Hole Informationen über den zu erstellenden Graphen
	$select = "SELECT * FROM Graph WHERE ID = '".$ID."';";
    $graphresult = $db->query($select);
    $grapheintrag = $graphresult->fetchArray();

	//Hole Informationen wo das Graphendiagramm gespeichert werden soll
	//und ob eine Kopie im IPS Mediaordner angelegt werden soll
	//und ob zwei Y Achse benutzt werden dürfen
	$select = "SELECT * FROM Zusatz;";
	$zusatzresult = $db->query($select);
	while($eintrag = $zusatzresult->fetchArray())
	{
		if($eintrag['Bezeichnung'] == "kopierezuIPSMedia")	{$kopierezuIPSMedia = (int)$eintrag['Wert'];}
		if($eintrag['Bezeichnung'] == "registriereMedia")	{$registriereMedia = (int)$eintrag['Wert'];}
		if($eintrag['Bezeichnung'] == "erwGraphenpfad")		{$graphspeicherpfad = $eintrag['Wert'];}
		if($eintrag['Bezeichnung'] == "erlaubeY2Achse")		{$erlaubeY2Achse    = (int)$eintrag['Wert'];}
		if($eintrag['Bezeichnung'] == "DUGMediaKategorieID"){$DUG_Media_ID    = (int)$eintrag['Wert'];}
	}
    //frage nach, wie viele Variablen in diesem Graphen dargestellt werden sollen
	$select = "SELECT COUNT(VarID) AS 'Anzahl' FROM Graphenliste WHERE GraphID = '".$grapheintrag['ID']."';";
	$countrequest = $db->query($select);
    $countresult = 	$countrequest->fetchArray();
	$variablenanzahl = $countresult['Anzahl'];


	//**********************************
	//******* Graphengrundeinstellung *****
	//**********************************

	// Groesse und Farbe des Graphen festlegen
	$graph = new Graph($grapheintrag['GroesseX'],$grapheintrag['GroesseY']);
	$graph->SetMargin(GRAPH_LINKER_RAND, GRAPH_RECHTER_RAND, GRAPH_OBERER_RAND, GRAPH_UNTERER_RAND+(ZEIGE_LEGENDE ? ($variablenanzahl*GRAPHEN_RANDERWEITERUNG_PRO_VARIABLE): 0));
	$graph->SetMarginColor(FARBE_GRAPHEN_RAND);
	$graph->SetColor(FARBE_PLOT_BEREICH);

	$graph->SetFrame(true,FARBE_BILD_RAHMEN, DICKE_BILD_RAHMEN);
	$graph->SetBox(true, FARBE_PLOT_RAHMEN, DICKE_PLOT_RAHMEN);


	// Ueberschrift erstellen
	$graph->title->SetFont(FF_ARIAL,FS_BOLD,14);
	$graph->title->Set($grapheintrag['Ueberschrift']);
	$graph->title->SetColor(FARBE_TITEL);
	$graph->subtitle->SetFont(FF_ARIAL,FS_NORMAL,9);
	$graph->subtitle->SetColor(FARBE_UNTERTITEL);
    $graph->subtitle->Set("erstellt am ".date("d.m.Y",time())." um ".date("H:i",time())." Uhr");


	//GraphenTyp == 0 -> Liniengraph
	if ($grapheintrag['GraphenTyp'] == 0)
	{
		//Skalierung für Y Achse wird auf automatisch gesetzt; X Achse ist fest von Null bis "DarstellungsIntervall"
		$graph->SetScale('intlin',0,0,0,$grapheintrag['DatenIntervall']);
		$graph->yaxis->scale->SetGrace(10);  //10% "Luft" nach oben
		$meineticks = BerechneTicks($grapheintrag['GroesseX'] - GRAPH_LINKER_RAND - GRAPH_RECHTER_RAND, $grapheintrag['DatenIntervall'] );
		$graph->xaxis->SetTickPositions($meineticks[0],$meineticks[1]);
	}
	else
	{
		//Skalierung für Y Achse wird auf automatisch gesetzt; X Achse ist fest von Null bis "DarstellungsIntervall" Manuell beschriftet
		$graph->SetScale('textlin');
		$graph->yaxis->scale->SetGrace(10);  //10% "Luft" nach oben
		$graph->xaxis->SetTickLabels(BerechneBarTicks($graph, $grapheintrag['GroesseX'] - GRAPH_LINKER_RAND - GRAPH_RECHTER_RAND, $grapheintrag['GroesseY']+8-(GRAPH_UNTERER_RAND+(ZEIGE_LEGENDE ? ($variablenanzahl*GRAPHEN_RANDERWEITERUNG_PRO_VARIABLE): 0)), $grapheintrag['DatenIntervall'], $grapheintrag['BarGraphIntervall']));

	}

	// Gitternetz im Graphen
	$graph->xgrid->Show(ZEIGE_XGITTER_HAUPTLINIEN, ZEIGE_XGITTER_ZWISCHENLINIEN);
	$graph->xgrid->SetWeight(1);
	$graph->xgrid->SetLineStyle('dashed','dotted');
	$graph->xgrid->SetColor(FARBE_XGRID_LINIEN);
	$graph->xgrid->SetFill(XGITTER_ALTERNIEREND_FUELLEN, FARBE_XGITTER_FUELLEN_1, FARBE_XGITTER_FUELLEN_2);


	$graph->ygrid->Show(ZEIGE_YGITTER_HAUPTLINIEN, ZEIGE_YGITTER_ZWISCHENLINIEN);
	$graph->ygrid->SetLineStyle('solid');
	$graph->ygrid->SetColor(FARBE_YGRID_LINIEN);
	$graph->ygrid->SetFill(YGITTER_ALTERNIEREND_FUELLEN, FARBE_YGITTER_FUELLEN_1, FARBE_YGITTER_FUELLEN_2);



	//Ausrichtung Beschriftung der X-Achse bestimmen.
	$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph->xaxis->SetLabelAlign('center','top');
	$graph->xaxis->SetPos('min'); //X Achse am unteren Rand
	$meineBeschriftung = new CBeschriftung($grapheintrag['DatenIntervall'],0);
	$graph->xaxis->SetLabelFormatCallback(array($meineBeschriftung,'erstelleBeschriftung'));
    $graph->xaxis->SetColor(FARBE_X_ACHSE);


	//Schriftart und Beschriftung der Y-Achse
	$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph->yaxis->title->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph->yaxis->title->SetAngle(0);
	$graph->yaxis->SetColor(FARBE_Y1_ACHSE);

	//Legende konfigurieren
	$meineLegende->Hide(!ZEIGE_LEGENDE);
	$meineLegende->Pos(LEGENDE_X_POS, LEGENDE_Y_POS,"left","bottom");
	$meineLegende->SetFont(FF_ARIAL,FS_NORMAL,9);
	$meineLegende->SetShadow(ZEIGE_LEGENDEN_SCHATTEN, DICKE_LEGENDEN_SCHATTEN);
	$meineLegende->SetColor(FARBE_LEGENDEN_SCHRIFT,FARBE_LEGENDEN_RAHMEN);
	$meineLegende->SetFrameWeight(DICKE_LEGENDEN_RAHMEN);
	$meineLegende->SetFillColor(FARBE_LEGENDE_FUELLEN);
	$meineLegende->SetColumns(5);
	$meineLegende->Add('Name:',FARBE_LEGENDE_FUELLEN,$myDummyPlotMark,1);
	$meineLegende->Add('Aktuell:',FARBE_LEGENDE_FUELLEN,$myDummyPlotMark,1);
	$meineLegende->Add('Max:',FARBE_LEGENDE_FUELLEN,$myDummyPlotMark,1);
	$meineLegende->Add('Min:',FARBE_LEGENDE_FUELLEN,$myDummyPlotMark,1);
	$meineLegende->Add('Avg:',FARBE_LEGENDE_FUELLEN,$myDummyPlotMark,1);

	//**********************************
	//***ENDE  Graphengrundeinstellung ***
	//**********************************


	//hole die Informationen über die Variablen, die im Graphen angezeigt werden sollen -> Variablen werden aufsteigend zu ihren ZWerten (Tiefe) angelegt.
	$select = "SELECT * FROM Graphenliste WHERE GraphID = '".$grapheintrag['ID']."' ORDER BY ZWert ASC;";
	$varresult = $db->query($select);

	$deaktiviereEinheitenAnzeige = false; //wenn mehrere Variablen verschiedene Einheiten haben und keine Zweite Y Achse benutzt werden soll, soll gar keine Einheit angezeigt werden

	if ($grapheintrag['GraphenTyp'] != 0)
	//**********************************
	//*******  Balkengraph erstellen     *****
	//**********************************
	{
		//Bei Balkengraphen ergibt sich das Darstellungsintervall aus der gerundeten Balkenanzahl mal dem Balkenintervall.
		//damit soll verhindert werden,  dass bei bestimmten Darstellungs- und BalkenIntervallen, z.b. 6 Monate mit 14 Tagen Intervall
		//der erste Balken nur "halb" mit Daten gefüllt ist,  weil 6 Monate nicht ganzzahlig durch 14 Tage teilbar ist
		$maximaleBalkenAnzahl = round(($grapheintrag['DatenIntervall']/$grapheintrag['BarGraphIntervall']));
		$darstellungsIntervall = $maximaleBalkenAnzahl*$grapheintrag['BarGraphIntervall'];

		//Zeitpunkt an dem der Graph starten soll wird berechnet
		$startzeit = time() - $darstellungsIntervall;

		//Durchlaufe alle anzuzeigenden Variablen
		while($varlist = $varresult->fetchArray())
		{


			//hole alle Werte der anzuzeigenden Variablen sortiert aufsteigend nach der Zeit (Zeitsortierung wichtig für die Graphdarstellung)
			$select = "SELECT * FROM VarEreignis WHERE VarID = '".$varlist['VarID']."' AND DatumZeit >= '".$startzeit."'  ORDER BY DatumZeit ASC;";
			$varevent = $db->query($select);

			//frage das erste Elemtent vor der gegebenen Zeitschranke ab
			//dies ist wichtig um den Anfang des Graphen richtig darzustellen
			//liegt ein Wert vor der zeitschranke und einer  Weit dahinter  so muss für die Zeit vom Nullpunkt bis zum ersten Datenpunkt der letzte
			//bekannt Wert angezeigt werden. Gibt es vor der Zeitschranke keinen Wert, so wird 0 angezeigt
			$select = "SELECT *  FROM VarEreignis WHERE VarID = '".$varlist['VarID']."' AND DatumZeit < '".$startzeit."'  ORDER BY DatumZeit DESC LIMIT 1;";
			$lastvaluequery = $db->query($select);
			$lastvalueresult = $lastvaluequery->fetchArray();


			if (isset($lastvalueresult['Wert']))
			{
					//gibt es einen Wert vor dem Dartstellungsintervall, so wird dieser Wert genommen
					$erstesDatumVorDatenIntervall= new CDatenpunkt($lastvalueresult['DatumZeit'], $lastvalueresult['Wert']);
			}
			else
			{
				$erstesDatumVorDatenIntervall= new CDatenpunkt(0,0);
			}




			$i=0;
			$datenArray = array();
			while($eventrow = $varevent->fetchArray())
			{
				$datenArray[$i] = new CDatenpunkt((int)$eventrow['DatumZeit'], $eventrow['Wert']);
				$i++;
			}

			//das aktuelle Datum (Datensatz aus Wert und Zeitpunkt) wird extra gespeichert um ihn in der Legende anzuzeigen
			if ($i == 0)
			{
				$aktuellesDatum = $erstesDatumVorDatenIntervall;
			}
			else
			{
				$aktuellesDatum = end($datenArray);
			}

			// 1 = Maximum
			//2= Minimum
			//3= Mittelwert
			//4= Summe
			switch ($grapheintrag['GraphenTyp'])
			{
				case '1': 	benutzefilter($datenArray, $erstesDatumVorDatenIntervall, $darstellungsIntervall, 'maximum('.$grapheintrag['BarGraphIntervall'].')');
					break;
				case '2': 	benutzefilter($datenArray, $erstesDatumVorDatenIntervall, $darstellungsIntervall, 'minimum('.$grapheintrag['BarGraphIntervall'].')');
					break;
				case '3': 	benutzefilter($datenArray, $erstesDatumVorDatenIntervall, $darstellungsIntervall, 'mittelwert('.$grapheintrag['BarGraphIntervall'].')');
					break;
				case '4': 	benutzefilter($datenArray, $erstesDatumVorDatenIntervall, $darstellungsIntervall, 'summe('.$grapheintrag['BarGraphIntervall'].')');
					break;

			}


			//Datensatz in das richtig Format konvertieren und Maximum, minimum bestimmen
			$ymax = $datenArray[0]->y;
			$ymin = $datenArray[0]->y;
			$yDaten = array();
			$j =0;
			foreach($datenArray as $datum)
			{
				$yDaten[$j] = $datum->y;
				$j++;

				//Maximum / Minimum bestimmen -> für Legende
				if($ymax < $datum->y){$ymax = $datum->y;}
				if($ymin > $datum->y){$ymin = $datum->y;}
			}


			$bar = new BarPlot($yDaten);
			$bar->value->Show(BALKEN_ZEIGE_WERTE);
			$bar->value->SetAngle(BALKEN_WERTE_WINKEL);
			$bar->value->SetColor(FARBE_BALKEN_WERTE);
			$bar->SetValuePos(BALKEN_WERTE_POSITION);
			$bar->SetColor("#".$varlist['VarFarbe']);
			$bar->SetWeight($varlist['LinienTyp']%10);
			//berechnet, ob "linientyp" > 10 ist und damit der Graph gefüllt werden soll
			//füllwert enthält die Durchsichtigkeitswert der Farbe
			//mit 1-$fuellwert wird daraus der Kehrwert und somit die Deckkraft
			$fuellwert = (floor($varlist['LinienTyp']/10))/10;
			if ($fuellwert)
			{
				$bar->SetFillColor("#".$varlist['VarFarbe']."@".(1-$fuellwert));
				$FarbeLegendenEintrag = "#".$varlist['VarFarbe']."@".(1-$fuellwert);
			}
			else
			{
				$bar->SetFillColor("#".$varlist['VarFarbe']."@1.0");
				$FarbeLegendenEintrag = "#".$varlist['VarFarbe']."@0";
			}


			//hole die Informationen über die Variablen, die im Graphen angezeigt werden sollen
			$select = "SELECT * FROM Variable WHERE ID = '".$varlist['VarID']."';";
			$vardataresult = $db->query($select);
			$vardata = $vardataresult->fetchArray();

			//eine Zweite y Achse ist bei Byrgraphen nicht möglich
			$nutzeY2Achse = false;

			//abfagen, ob diese Variablen eine physikalische Einheit hat und diese dann setzen
			//Angezeigt wird die Einheit der letzten Variablen, die einen Eintrag für ihre Einheit hat
			if ((isset($vardata['Einheit'])) && (strlen($vardata['Einheit'])!=0)&& ($vardata['Einheit']!= "unbekannt"))
			{
				if ($physEinheitY == "")
				{
					$physEinheitY  = $vardata['Einheit'];
				}
				else
				{
					//wenn die Einheit der ersten Achse nicht mit der Einheit der variable übereinstimmt
					// wird die Einheitenbeschriftung deaktiviert
					if ($physEinheitY != $vardata['Einheit'])
					{
						$deaktiviereEinheitenAnzeige = true;
					}
				}
			}

			//Maximum, minimum und Durschnitt berechnen
			$yavg = (float)(array_sum($yDaten)/count($yDaten));



			//Legende anlegen
			$dummyPlotMark = $myDummyPlotMark;
			$meineLegende->Add($vardata['Name'],$FarbeLegendenEintrag,'',1);
			$meineLegende->Add(round($aktuellesDatum->y,1).$vardata['Einheit'],FARBE_LEGENDE_FUELLEN,$dummyPlotMark,1);
			$meineLegende->Add(round($ymax,1).$vardata['Einheit'],FARBE_LEGENDE_FUELLEN,$dummyPlotMark,1);
			$meineLegende->Add(round($ymin,1).$vardata['Einheit'],FARBE_LEGENDE_FUELLEN,$dummyPlotMark,1);
			$meineLegende->Add(round($yavg,1).$vardata['Einheit'],FARBE_LEGENDE_FUELLEN,$dummyPlotMark,1);




			$barplot[] = $bar;

		}



		$aktiviereExtraNulllinie = FALSE;
		$gbplot  = new GroupBarPlot ($barplot);
		$graph->Add( $gbplot);
	}
	//**********************************
	//***** ENDE  Balkengraph erstellen  ***
	//**********************************
	else
	//**********************************
	//*******  Liniengraph erstellen     *****
	//**********************************
	while($varlist = $varresult->fetchArray())
	{
		$ydata = array();
		$xdata = array();


		//frage ab,  wie viele Werte es im darzustellenden  Zeitrahmen gibt
		$select = "SELECT COUNT(ID) as Anzahl FROM VarEreignis WHERE VarID = '".$varlist['VarID']."' AND DatumZeit >= '".(time()-$grapheintrag['DatenIntervall'])."'  ORDER BY DatumZeit ASC;";
		$countquery = $db->query($select);
		$countresult = $countquery->fetchArray();

		//Anzahl der darzustellenden Werte: 0 -> zeichne Linie mit letztem Wert;   > 0 -> zeichne normalen Graphen
		if ($countresult['Anzahl'])
		{
			//hole alle Werte der anzuzeigenden Variablen sortiert aufsteigend nach der Zeit (Zeitsortierung wichtig für die Graphdarstellung)
			$select = "SELECT * FROM VarEreignis WHERE VarID = '".$varlist['VarID']."' AND DatumZeit >= '".(time()-$grapheintrag['DatenIntervall'])."'  ORDER BY DatumZeit ASC;";
			$varevent = $db->query($select);
			$eventrow = $varevent->fetchArray();

			//den Durchscnhittswert aller gerade dargestellten Werte ausgeben
			$select = "SELECT AVG(Wert) AS 'Durchschnitt' FROM VarEreignis WHERE VarID = '".$varlist['VarID']."' AND DatumZeit >= '".(time()-$grapheintrag['DatenIntervall'])."'  ORDER BY DatumZeit ASC;";
			$avgrequest = $db->query($select);
			$avgresult = $avgrequest->fetchArray();


			//überprüfe, ob der erste Datenwert auf dem Nullpunkt der vorgegebenen Zeitachse ist
			//falls ja, wird die Zeitachse korrekt dargestellt und die Werte werden einfach übernommen
			//falls nein, würde sich die Zeitachse verschieben, darum wird der erste Wert vor dem Darstellungsintervall abgefragt
			//Problembeispiel: Darstellungsintervall (Zeitachse) sei 3600s, der erste Variablenwert kommt erst bei 1800s. Die ZEit von 0s bis 1800s muss
			//nun mit dem ersten Wert vor dem Darstellungsintervall aufgefüllt werden.
			//Falls es vorher keinen git, wird der Wert 0 genommen (ist der Mittelwert aus Darstellungsgründen hier vielleicht besser? -> Graphskalierung)
			if ($eventrow['DatumZeit'] == (time()-$grapheintrag['DatenIntervall']))
			{
				$ydata[0]= $eventrow['Wert'];
				$xdata[0]= (int)($eventrow['DatumZeit']-$grapheintrag['DatenIntervall']);

			}
			else
			{
				//frage das erste Elemtent vor der gegebenen Zeitschranke ab
				$select = "SELECT *  FROM VarEreignis WHERE VarID = '".$varlist['VarID']."' AND DatumZeit < '".(time()-$grapheintrag['DatenIntervall'])."'  ORDER BY DatumZeit DESC LIMIT 1;";
				$lastvaluequery = $db->query($select);
				$lastvalueresult = $lastvaluequery->fetchArray();

				if (isset($lastvalueresult['Wert']))
				{
					//gibt es einen Wert vor dem Dartstellungsintervall, so wird dieser Wert genommen
					$ydata[0]= $lastvalueresult['Wert'];
					$xdata[0]= 0;
				}
				else
				{
					//gibt es keinen Wert vor dem Dartstellungsintervall, so wird der Wert 0 genommen
				//	$ydata[0]= 0;
				//	$xdata[0]= 0;
				//***** gar nichts anzugeben hat sich als schöner herausgestellt -> Graphenlinie fängt  ab da an, wo es die ersten Werte gibt
				}
			}

			//die benutzte Zeitachse geht von 0sek bis zum im Graphen  eingestellten Wert des DarstellungsIntervalls
			//Die Zeitachsenwerte werden anschließend in die richtigen Zeitwerte umgerechnet
			//Umrechnung -> siehe Anfang vom dieser Datei -> klasse CBeschriftung -> erstelleBeschriftung
			$startzeit = (time()-(int)$grapheintrag['DatenIntervall']);
			$endzeit = (int)$grapheintrag['DatenIntervall'];
			$letzterwert = 0;

			$i=count($ydata);
			while($eventrow = $varevent->fetchArray())
			{
				$ydata[$i]= $eventrow['Wert'];
				$letzterwert = $eventrow['Wert'];
				$xdata[$i]= (int)$eventrow['DatumZeit']-$startzeit;
				$i=$i+1;
			}
			//genau wie vom Nullpunkt zum ersten Datenwert, muss vom letzten Datenwert zum Ende des Graphen die Linie
			//fortgesetzt werden, falls der letzte Datenwert nicht genau auf das Ende fällt
			$i=count($ydata);
			$ydata[$i]=  $letzterwert;
			$xdata[$i]= $endzeit;
		}
		else
		{
			//frage das erste Elemtent vor der gegebenen Zeitschranke ab  (unter der Annahme, das mindestens ein Wert existiert)
			$select = "SELECT * FROM VarEreignis WHERE VarID = '".$varlist['VarID']."' AND DatumZeit < '".(time()-$grapheintrag['DatenIntervall'])."'  ORDER BY DatumZeit DESC LIMIT 1;";
			$lastvaluequery = $db->query($select);
			$lastvalueresult = $lastvaluequery->fetchArray();


			//sollte  es für den  darzustellenden Zeitraum keine Daten geben, wird der letzte bekannte Wert genommen und eine Gerade mit diesem
			//Wert über die gesamte Zeitspanne eingezeichnet
			$ydata[0]= $lastvalueresult['Wert'];
			$xdata[0]= 0;
			$ydata[1]= $lastvalueresult['Wert'];
			$xdata[1]= $grapheintrag['DatenIntervall'];
			$endzeit = $grapheintrag['DatenIntervall'];

			$letzterwert = $lastvalueresult['Wert'];
		}

		//hole die Informationen über die Variablen, die im Graphen angezeigt werden sollen
		$select = "SELECT * FROM Variable WHERE ID = '".$varlist['VarID']."';";
		$vardataresult = $db->query($select);
		$vardata = $vardataresult->fetchArray();


		//vor einem möglichen Filtern wird der aktuelle Wert extra gespeichert um ihn in der Legende anzuzeigen
		$aktuellesDatum = new CDatenpunkt(end($xdata),end($ydata));

		//falls gewünscht, einen Filter auf die Werte anwenden -> siehe filter.php
		//filter($xdata, $ydata, $varlist['Filter']);
		//filter($xdata, $ydata, 'summe(0,86400)');

		// Graphenlinie erzeugen
		$line = new LinePlot($ydata,$xdata);


		$line->SetColor("#".$varlist['VarFarbe']);
		//ein Wert von 0-9 gibt die Liniendicke an -> Werte über 10 geben den Füllgrad ind zehnerschritten an
		$line->SetWeight($varlist['LinienTyp']%10);

		$FarbeLegendenEintrag = "#".$varlist['VarFarbe'];

		//berechnet, ob "linientyp" > 10 ist und damit der Graph gefüllt werden soll
		//füllwert enthält die Durchsichtigkeitswert der Farbe
		//mit 1-$fuellwert wird daraus der Kehrwert und somit die Deckkraft
		$fuellwert = (floor($varlist['LinienTyp']/10))/10;
		//wird gesetz um in der Legende eine Linie zur Identifikation der Variable zu zeichnen
		//wurde ein Füllfarbe ausgewähl, so muss $dummyPlotmark leer sein, um statt der Linie ein  Kästchen in der Legende anzuzeigen
		$dummyPlotMark = $myDummyPlotMark;
		if ($fuellwert)
		{
			// Design Verbesserung der Graphen (Fläche unter dem Graphen füllen)
			$line->SetFillColor("#".$varlist['VarFarbe']."@".(1-$fuellwert));
			$dummyPlotMark = '';
			$FarbeLegendenEintrag = "#".$varlist['VarFarbe']."@".(1-$fuellwert);
		}

		// Variablen, die nicht vom Typ Float sind (Integer und Boolean), werden als Treppenstufen angezeigt; ohne Zwischenwerte
		if ($varlist['DarGanzzahl'] == 1)
		{
			$line->SetStepStyle(true);
		}


		//anhand der nachfolgend zugewiesenen Einheit soll bestimmt werden, ob eine zweite Y Achse angelegt werden soll
		$nutzeY2Achse = false;

		//abfagen, ob diese Variablen eine physikalische Einheit hat und diese dann setzen
		//Angezeigt wird die Einheit der letzten Variablen, die einen Eintrag für ihre Einheit hat
		if ((isset($vardata['Einheit'])) && (strlen($vardata['Einheit'])!=0)&& ($vardata['Einheit']!= "unbekannt"))
		{
			if ($physEinheitY == "")
			{
				$physEinheitY  = $vardata['Einheit'];
			}
			else
			{
				//wenn die Einheit der ersten Achse nicht mit der Einheit der variable übereinstimmt
				//soll eine zweite Y Achse angelegt werden, wenn dies in der DB erlaubt worden ist
				//ansonsten wird die Einheitenbeschriftung deaktiviert
				if ($physEinheitY != $vardata['Einheit'])
				{
						if($erlaubeY2Achse)
						{
							$physEinheitY2 = $vardata['Einheit'];
							$nutzeY2Achse = true;
						}
						else
						{
							//wenn mehrere Variablen verschiedene Einheiten haben und keine zweite Y Achse benutzt werden soll, soll gar keine Einheit angezeigt werden
							$deaktiviereEinheitenAnzeige = true;
						}
				}
			}
		}


		// Max / Min Wert berechnen; Legende anlegen und  Max Min Werte einbinden
		//wenn Informationen über den Durchschnitt existieren soll dieser auch angezeigt werden
		// -> dabei handelt es sich nur um den Durchschnitt über den gespeicherten Werten, nicht um den Durchschnitt des gesamten Graphen!!!
		list($xmin,$ymin) = $line->Min();
		list($xmax,$ymax) = $line->Max();

		if (isset($avgresult)){$yavg = round($avgresult['Durchschnitt'],1);}
		else {$yavg = end($ydata);}

		$meineLegende->Add($vardata['Name'],$FarbeLegendenEintrag,$dummyPlotMark,1);
		//wird gesetz um in der Legende eine Linie zur Identifikation der Variable zu zeichnen
		//wurde ein Füllfarbe ausgewähl, so muss $dummyPlotmark leer sein, um statt der Linie ein  Kästchen in der Legende anzuzeigen
		$dummyPlotMark = $myDummyPlotMark;

		$meineLegende->Add(round($aktuellesDatum->y,1).$vardata['Einheit'],FARBE_LEGENDE_FUELLEN,$dummyPlotMark,1);
		$meineLegende->Add(round($ymax,1).$vardata['Einheit'],FARBE_LEGENDE_FUELLEN,$dummyPlotMark,1);
		$meineLegende->Add(round($ymin,1).$vardata['Einheit'],FARBE_LEGENDE_FUELLEN,$dummyPlotMark,1);
		$meineLegende->Add(round($yavg,1).$vardata['Einheit'],FARBE_LEGENDE_FUELLEN,$dummyPlotMark,1);


		//zusätzlich Nulllinie erzeugen ,wenn ein y Wert unter 0 liegt
		if ($ymin < 0) {$aktiviereExtraNulllinie = TRUE;}
		else {$aktiviereExtraNulllinie = FALSE;}


		//wenn die zweite Y Achse genutzt werden soll wird sie im if block aktiviert
		//ihre Farbe auf die der letzten Linienfarbe gesetzt
		//und die Daten für die line eingezeichnet
		if ($nutzeY2Achse)
		{
			$graph->SetY2Scale('lin');
			//$graph->y2axis->SetColor("#".$varlist['VarFarbe']);
		    $graph->y2axis->SetColor(FARBE_Y2_ACHSE);
			$graph->AddY2($line);
		}
		else
		{
			$graph->Add($line);
		}
	}

	//**********************************
	//***** ENDE  Liniengraph erstellen  ***
	//**********************************

	//zusätzlich Nulllinie erzeugen ,wenn ein y Wert unter 0 liegt
	if ($aktiviereExtraNulllinie)
	{
		$nulllinie = new PlotLine(HORIZONTAL,0,"black",2);
		$nulllinie->SetColor(FARBE_EXTRA_NULLLINIE);
		$graph->AddLine($nulllinie);
	}

	//Die phys. Einheitenbeschreibung der Y-Achse setzen
	if (!$deaktiviereEinheitenAnzeige)
	{
		if ($physEinheitY == "") {$physEinheitY = "unbekannt";};
		$einheit1=new Text($physEinheitY  ,GRAPH_LINKER_RAND-20,GRAPH_OBERER_RAND-25);
		$einheit1->SetFont(FF_ARIAL,FS_BOLD,10);
		$einheit1->SetColor(FARBE_EINHEIT_Y1);
		$graph->AddText($einheit1);


		//Die phys. Einheitenbeschreibung der Y2-Achse setzen
		if ($physEinheitY2 != "")
		{
			$einheit2=new Text($physEinheitY2);
			$einheit2->SetPos($grapheintrag['GroesseX']-((GRAPH_RECHTER_RAND-25)+$einheit2->GetWidth($graph->img)),GRAPH_OBERER_RAND-25);
			$einheit2->SetFont(FF_ARIAL,FS_BOLD,10);
			$einheit2->SetColor(FARBE_EINHEIT_Y2);
			$graph->AddText($einheit2);
		}
	}


	//Pfad und Dateinamen zusammensetzen und Graphen erstellen
    $graphdateiname = $grapheintrag['Name']."_".$grapheintrag['ID'].".png";
	$graphpfad = $graphspeicherpfad.$graphdateiname;


	//$graph->Stroke($graphpfad);
	$img = $graph->Stroke(_IMG_HANDLER);
	$meineLegende->Stroke($img);
	$mycache = new ImgStreamCache($img);
	$mycache->PutAndStream($img,"",false,$graphpfad);

	//füge das Diagramm als Media File in IPS hinzu
	if ($registriereMedia)
	{
		exist_media($graphspeicherpfad, $graphdateiname, $DUG_Media_ID, $grapheintrag['Ueberschrift'], $grapheintrag['Name']);
		//kopieren wurde durch obige Zeile ersetzt -> Somit kennt IPS die Graphendatei
		//copy($graphpfad,IPS_GetKernelDir()."media\\".$graphdateiname);
	}


	$db->close();
	return $graphdateiname;
}




?>
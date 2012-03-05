<?
//==================================================================================
// Datei.......: filter.php
// Beschreibung: wendet den ausgewählten Filter auf einen Datensatz an
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 40 $
// zuletzt geändert : 	$Date: 2009-05-29 15:53:44 +0200 (Fr, 29 Mai 2009) $
// Author:			$Author: tobias $
//==================================================================================

function benutzefilter(&$daten, $letztesDatum, $datenIntervall, $filtername)
{
	global $DUGTOOLPFAD;
	if ($filtername != 'keinFilter')
	{
		$filterliste = explode(";",$filtername);
		foreach($filterliste as $meinFilter)
		{
			$filtername = strtok($meinFilter,"(");
			$parameterstring = strtok(")");
			$parameterliste = array();
			if ($parameterstring != '')
			{
				$parameterliste = explode(",",$parameterstring);
				//echo "Benutze Filter: '".$filtername."' mit den Parametern '".$parameterstring."'<br>";
			} else
			{
				//echo "Benutze Filter: '".$filtername."' ohne Parameter.<br>";
			}
			if (file_exists($DUGTOOLPFAD."filter/".$filtername.".php"))
			{
				//echo "Filter gefunden<br>";
				require_once($DUGTOOLPFAD."filter/".$filtername.".php");
				$filterfunktion = $filtername;

				$filterfunktion($daten, $letztesDatum, $datenIntervall, $parameterliste);
			}
			else
			{
				echo "Das von Ihnen angegebene Filter mit dem Namen '".$filtername."' konnte nicht gefunden werden!<br>";
			}
			
		
		}
	}
}

function erstelleDatenBloecke(&$datenArray, $blockIntervall, $datenIntervall, $letzterWert)
{
	$verschiebung = ($datenIntervall % $blockIntervall);
	//$verschiebung =0;
	$startzeit = time() - $datenIntervall;
	//wenn das Bargraph Intervall größer als das DatenIntervall ist, soll nur ein Balken über  das gesamte DatenIntervall angezeigt werden
	if ($datenIntervall < $blockIntervall)
	{
		$maximaleBlockAnzahl = 1;
	} else 
	{
		$maximaleBlockAnzahl = round($datenIntervall/$blockIntervall); 
	}
	
	//der erste Eintrag im ersten Block ist der letzte Wert vor dem Darstellungsintervall
	//würde man das nicht machen und der erste Wert im ersten Block wäre zum Zeitpunkt t=10s
	//wären die Werte zwischen t=0 und t=10 undefiniert
	$letzterWert->x = $startzeit;	
	$alleDaten = array(array($letzterWert));
	
	$aktuellerBlock = 0;
	foreach($datenArray as $datum)	
	{
		//berechne in welchen Block die Variablenwerte gehören
		$blockNummer = floor(($datum->x-$startzeit+$verschiebung) / $blockIntervall);
		
		//wenn die Werte zum  aktuellen Balken gehören so schreibe die Werte zu dessen Datensatz
		//wenn sie nicht dazu gehören...
		if($blockNummer == $aktuellerBlock)
		{
			//liegt der erste Datensatz genau auf dem Nullpunkt ( t = 0) so überschreibe den vorher
			//gesetzten Startwert, der "$letzterWert" war
			if(($aktuellerBlock == 0)&&(($datum->x-$startzeit+$verschiebung) == 0)) 
			{				
				$alleDaten[$aktuellerBlock][0] = $datum;		
			}
			else
			{ 
				$alleDaten[$aktuellerBlock][] = $datum;	
			}
		}
		else
		{
			
			//beträgt der Unterschied zwischen den Balkennummern nur 1, so setze den $aktuellenBalken Zähler
			//um eins nach oben und setze den Variablenwert in den neuen Balken
			//ist der Unterschied größer bsp. aktuellerBalken = 5 und $balkenNummer = 10
			//so fülle die Balken dazwischen mit dem letzten Wert aus Balken 5
			if($blockNummer - $aktuellerBlock == 1)
			{						
				$aktuellerBlock = $blockNummer;
				$alleDaten[$aktuellerBlock][] = $datum;						
			}
			else
			{
				//es wird mit dem nächsten Balken weiter gemacht. Da für diesen keine neuen Werte zur Verfügung stehen, wird
				//der Letzte Wert dem neuen Balken zugewiesen	
				$aktuellerBlock = $aktuellerBlock+1; 
				for($aktuellerBlock; $aktuellerBlock < $blockNummer; $aktuellerBlock++)
				{
					$letzterWert->x = $startzeit+($aktuellerBlock * $blockIntervall);
					$alleDaten[$aktuellerBlock][] = $letzterWert;							
				}
						
				//nun gehen wir weiter zum nächsten Balken, für den es wieder Werte gibt 
				$alleDaten[$aktuellerBlock][] = $datum;
			}
		}
		//merke dir den letzten Wert und damit evtl. Zwischenbalken zu "füllen"			
		$letzterWert = $datum;
	}	
		
		//if Abfrage behandelt den Sonderfall, dass keine Werte für die vorgegebene Zeitspanne in der DB war
		//somit ist die while schleife auch nicht durchlaufen worden und $aktuellerBalken immer noch bei 0
		if ($aktuellerBlock != 0) {$aktuellerBlock = $aktuellerBlock +1;} 
		for($aktuellerBlock; $aktuellerBlock < ($maximaleBlockAnzahl); $aktuellerBlock++)
		{
			$letzterWert->x = $startzeit+($aktuellerBlock * $blockIntervall);
			$alleDaten[$aktuellerBlock][] = $letzterWert;

		}

	$datenArray = $alleDaten;
}
?>

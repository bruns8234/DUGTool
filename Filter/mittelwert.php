<?
// Filter, das den Mittelwert aller Y Wert bestimmt und als einziges wieder zurück gibt
//Parameterlist; 	[0]. Zeitintervall, innerhalb dessen alle Werte zu einer Summe zusammegefasst werden sollen
//			[1]. Anzahl der Werte, die zu einer Summe zusammengefasst werden sollen
function mittelwert(&$daten, $letztesDatum, $datenIntervall, $parameterliste)
{
	erstelleDatenBloecke($daten, $parameterliste[0], $datenIntervall, $letztesDatum);
	$i = 1;
	$tempdaten;	
	$startZeit = $daten[0][0]->x;
	$letzterEintrag = $daten[0][0];
	foreach($daten as $block)
	{		
		$meineSumme = 0;
		$tempZeit = (time()-$datenIntervall+(($i)*$parameterliste[0]));
		foreach($block as $datum)
		{
			$dauer = $datum->x-$letzterEintrag->x;
			$meineSumme += $letzterEintrag->y*$dauer;
			$letzterEintrag = $datum;		
		}
		$dauer = $tempZeit-$letzterEintrag->x;
		$meineSumme += $letzterEintrag->y*$dauer;
		$letzterEintrag->x = $tempZeit;
		
		
		$meinMittel = $meineSumme / $parameterliste[0];
		$tempdaten[] = new CDatenpunkt($tempZeit,$meinMittel);
		$i++;
	}

	$daten = $tempdaten;
}

?>
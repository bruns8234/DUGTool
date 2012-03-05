<? 
// Filter, das den größten Y Wert bestimmt und als einziges wieder zurück gibt
//Parameterlist; 	[0]. Zeitintervall, innerhalb dessen alle Werte zu einer Summe zusammegefasst werden sollen
//			[1]. Anzahl der Werte, die zu einer Summe zusammengefasst werden sollen
function maximum(&$daten, $letztesDatum, $datenIntervall, $parameterliste)
{
	erstelleDatenBloecke($daten, $parameterliste[0], $datenIntervall, $letztesDatum);
	$i = 1;
	$tempdaten;
	foreach($daten as $block)
	{
		
		$meinMaximum = $block[0]->y;
		foreach($block as $datum)
		{
			if ($datum->y > $meinMaximum) {$meinMaximum = $datum->y;}		
		}
		$zeitstempel = time()-$datenIntervall+($i*$parameterliste[0]);
		$tempdaten[] = new CDatenpunkt($zeitstempel,$meinMaximum);
		$i++;
	}
	$daten = $tempdaten;
}
?>
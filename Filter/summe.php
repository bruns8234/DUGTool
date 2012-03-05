<? 
// Filter, das die Summe aller Y Wert bestimmt und als einziges wieder zurück gibt
//Parameterlist; 	[0]. Zeitintervall, innerhalb dessen alle Werte zu einer Summe zusammegefasst werden sollen
//			[1]. Anzahl der Werte, die zu einer Summe zusammengefasst werden sollen
function summe(&$daten, $letztesDatum, $datenIntervall, $parameterliste)
{
	erstelleDatenBloecke($daten, $parameterliste[0], $datenIntervall, $letztesDatum);
	$i = 1;
	$tempdaten;
	foreach($daten as $block)
	{
		
		$meineSumme = 0;
		foreach($block as $datum)
		{
			$meineSumme = $meineSumme + $datum->y;			
		}
		$zeitstempel = time()-$datenIntervall+($i*$parameterliste[0]);
		$tempdaten[] = new CDatenpunkt($zeitstempel,$meineSumme);
		$i++;
	}
	$daten = $tempdaten;
}

?>
<? 
// eine Spline aus den Datenpunkten interpolieren 
// der Parameter ist die Anzahl der zu interpolierenden Datenpunkte

function spline(&$Xwerte, &$Ywerte, $parameterliste)
{	
		$line = new LinePlot($Ywerte,$Xwerte);
		$line = new Spline($Xwerte, $Ywerte);
		list($Xwerte, $Ywerte) = $line->Get($parameterliste[0]);
}
?>
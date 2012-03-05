<?	//Standardeinstellungen des DUG Tools

//***********************************
//******* Konstanten ab Version 1.4 *****	
//***********************************

//************								
//************  Einstellungen für das Erscheinungsbild des Graphendiagrammes ************
//************	

//die Abstände in denen "Ticks" auf der X Achse gezeichnet werden sollen -> für eine einheitliche Skalierung
//ein Wert wird gewählt, wenn der zeitabstand zwischen zwei Ticks einem Wert und seinem Vorgänger liegt
//Bsp.:  der Abstand zwischen zwei Ticks wäre 1017 Sek. -> als Schrittweite wird dann
//900 gewählt, da der Wert zwischen 750 (+1) und 1350 liegt
//Bsp.2: Tickabstand 77 -> gewählt wird 60 da 77 zwischen 45(+1) und 150 liegt
$GRAPHXACHSENTICKS = array( "10" => "20",//Ticks alle 10 Sek. 
							"30" => "45",//Ticks alle 30 Sek. 
							"60" => "150",//Ticks alle 60 Sek. 	
							"300" => "450",//Ticks alle 300 Sek. => 5 Minuten 	
							"600" => "750",//Ticks alle 600 Sek. => 10 Minuten 	
							"900" => "1350",//Ticks alle 900 Sek. => 15 Minuten 	
							"1800" => "2700",//Ticks alle 1800 Sek. => 30 Minuten 
							"3600" => "5400",//Ticks alle 3600 Sek. =>  1 Stunde 	
							"7200" => "10800",//Ticks alle 7200 Sek. =>  2 Stunde 
							"14400" => "18000",//Ticks alle 14400 Sek. =>  4 Stunde 
							"21600" => "32400",//Ticks alle 21600 Sek. =>  6 Stunde 	
							"43200" => "64800",//Ticks alle 43200 Sek. =>  12 Stunde 
							"86400" => "129600",//Ticks alle 86400 Sek. =>  24 Stunde 
							"172800" => "388800",//Ticks alle 172800 Sek. =>  2 Tage 
							"604800" => "907200",//Ticks alle 604800 Sek. =>  7 Tage 	
							"1209600" => "1900800",//Ticks alle 1209600 Sek. =>  14 Tage 	
							"2592000" => "9072000",//Ticks alle 2592000 Sek. =>  30 Tage 	
							"15552000" => "23544000",//Ticks alle 15552000 Sek. =>  180 Tage 	
							"31536000" => "999999999"//Ticks alle 31536000 Sek. =>  365 Tage 
							);



//Diagrammbreite durch diesen Wert gibt die ungefähre Anzahl benutzter X Achsenbeschriftungen an
//die genaue Anzahl wird anhand der $GRAPHXACHSENTICKS berechnet
define('ABSTAND_ZWISCHEN_HAUPTTICKS', 80);

//Ab welcher Pixelzahl die Zwischenticks angezeigt werden soll. 
//je kleiner der Wert, desto "überladener" sieht die X Achse aus
define('MINDESTABSTAND_ZWISCHEN_TICKS', 50);

//veschiebt die X Achsenbeschriftung um diese Sekundenzahl. Somit startet die Beschriftung bei einem 
//Graph mit 24 Std. Beschriftungsabstand immer um 0.00 Uhr
//ändert sich leider von Winter zu Sommerzeit.
//einfach mal einen 14 tägigen Graphen anlegen bei 800x600 und man sieht, was ich meine
//dieser Wert wird nur beachtet bei $BESCHRIFTUNGSTARTETBEINULL = FALSE;
define('XACHSEN_OFFSET', 7200);

//Ist dieser Wert auf True gestellt startet die Beschriftung der X Achse zur nächsten vollen Zeiteinheit, also z.b. zur nächsten vollen Stunde
//Bei FALSE start die Beschriftung bei X = 0 und kann auch "krumme" Zeitwerte, wie z.B. 15.27Uhr enthalten.
define('BESCHRIFTUNG_STARTET_BEI_NULL', TRUE);

//Ränder des Graphendiagramms
define('GRAPH_LINKER_RAND', 40);
define('GRAPH_RECHTER_RAND', 40);
define('GRAPH_OBERER_RAND', 40);
define('GRAPH_UNTERER_RAND', 75);
define('FARBE_BILD_RAHMEN', '#000000');
define('DICKE_BILD_RAHMEN', 3); // 0 = kein Rahmen 
define('FARBE_PLOT_RAHMEN', '#FFFFFF'); //weiß
define('DICKE_PLOT_RAHMEN', 0); // 0 = kein Rahmen 


//da jede Variable in der Legende vorkommt, wächst diese nach oben. Daher muss der untere Rand mitwachsen
define('GRAPHEN_RANDERWEITERUNG_PRO_VARIABLE', 15);

//Farbe des Graphenrandes
define('FARBE_GRAPHEN_RAND', 'lightblue@0.4');
define('FARBE_PLOT_BEREICH', '#FFFFFF@0.0'); //weiß

//Legende
define('ZEIGE_LEGENDE', TRUE);
define('ZEIGE_LEGENDEN_SCHATTEN', TRUE); //kann auch eine Farbe sein
define('DICKE_LEGENDEN_SCHATTEN', 2);
define('FARBE_LEGENDEN_RAHMEN', '#000000@0.0');
define('DICKE_LEGENDEN_RAHMEN', 1);
define('FARBE_LEGENDEN_SCHRIFT', '#000000@0.0');
define('FARBE_LEGENDE_FUELLEN', '#EFEFEF@0');

//Position der Legende in Prozent zum gesamten Bild
define('LEGENDE_X_POS', 0.02);
define('LEGENDE_Y_POS', 0.98);

//Farben der Achsen
define('FARBE_X_ACHSE', '#000000@0.0'); //schwarz
define('FARBE_Y1_ACHSE', '#000000@0.0');
define('FARBE_Y2_ACHSE', '#FF0000@0.0'); //rot

//farbe für der zusätlichen Nulllinie (wird angezeigt falls negative Werte im Plot vorkommen)
define('FARBE_EXTRA_NULLLINIE', '#000000@0.0');

//Gitternetzlinien
define('FARBE_XGRID_LINIEN', '#000000@0.9');
define('FARBE_YGRID_LINIEN', '#000000@0.9');
define('ZEIGE_XGITTER_HAUPTLINIEN',  TRUE);
define('ZEIGE_XGITTER_ZWISCHENLINIEN',  TRUE);
define('ZEIGE_YGITTER_HAUPTLINIEN',  TRUE);
define('ZEIGE_YGITTER_ZWISCHENLINIEN',  FALSE);

define('XGITTER_ALTERNIEREND_FUELLEN',  FALSE);
define('FARBE_XGITTER_FUELLEN_1',  '#EFEFEF@0.5');
define('FARBE_XGITTER_FUELLEN_2',  '#FFFFFF@0');

define('YGITTER_ALTERNIEREND_FUELLEN',  TRUE);
define('FARBE_YGITTER_FUELLEN_1',  '#EFEFEF@0.5');
define('FARBE_YGITTER_FUELLEN_2',  '#FFFFFF@0');

//Schriftfarben
define('FARBE_TITEL',  '#000000@0.0'); //schwarz
define('FARBE_UNTERTITEL',  '#000000@0.0'); //schwarz
define('FARBE_EINHEIT_Y1',  '#000000@0.0'); //schwarz
define('FARBE_EINHEIT_Y2',  '#000000@0.0'); //schwarz

//Filter angeben (Namen)
//Bsp.: $VERWENDEFILTER = 'verstaerken2x';
define('VERWENDE_FILTER',  ''); //keinen

//***********************************
//******* Konstanten ab Version 1.5 *****	
//***********************************	

define('BALKEN_XACHSENBESCHRIFTUNG_MITTIG', FALSE);

//der Wert des Balken wird IM Diagramm angezeigt
define('BALKEN_ZEIGE_WERTE', TRUE);
//erlaubte Werte 0 und 90 -> andere Werte ergeben einen Error
define('BALKEN_WERTE_WINKEL', 90); //Winkelangabe -> 0 = gerade
//erlaubte Werte 'top' 'center' 'bottom'
define('BALKEN_WERTE_POSITION', 'top');
//Farbe der Balkenbeschriftung
define('FARBE_BALKEN_WERTE', '#000000@0.0');

//wenn im Balkendiagramm die Schrittweite auf einen Monat steht, werden statt des Datums die Monatsnamen angezeigt
define('BALKEN_XACHSENBESCHRIFTUNG_MONATSNAMEN_ANZEIGEN', TRUE);
//wenn im Balkendiagramm die Schrittweite auf einen Tag steht, werden statt des Datums die Wochentagsnamen angezeigt
define('BALKEN_XACHSENBESCHRIFTUNG_WOCHENNAMEN_ANZEIGEN', TRUE);


							
?>

<?
//==================================================================================
// Datei.......: VerwaltungsKonfiguration_std.php
// Beschreibung: enthält die Standardeinstellungen des DUG Tools
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 38 $
// zuletzt geändert : 	$Date: 2009-05-29 00:18:31 +0200 (Fr, 29 Mai 2009) $
// Author:			$Author: schade $
//
// 23.10.2011 TGUSI74
// + SQLite3 Anpassungen
//==================================================================================

//

//***********************************
//******* Konstanten ab Version 1.4 *****
//***********************************


//Standardname für einen neuen Graphen
$GRAPHENNAME = "Mein Graph";

//Standardüberschrift für einen neuen Graphen
$GRAPHENUEBERSCHRIFT = "Meine Ueberschrift";

//Standardgrösse eines neuen Graphen in X Richtung
$GRAPHGROESSEX = 800;

//Standardgrösse eines neuen Graphen in Y Richtung
$GRAPHGROESSEY = 400;


//Farben mit denen die Linien des Graphen gezeichnet werden sollen
$FARBKONSTANTEN = array("000000" => "schwarz" ,
						"FFFFFF" => "weiss",
						"474747" => "grau1",
						"666666" => "grau2",
						"878787" => "grau3",
						"ABABAB" => "grau4",
						"D1D1D1" => "grau5",
						"FF0000" => "rot1",
						"CD0000" => "rot2",
						"EE5C42" => "rot3",
						"CD1076" => "rot4",
						"8B3A3A" => "rot5",
						"FF00FF" => "pink",
						"FF6600" => "orange1",
						"EE7600" => "orange2",
						"EE9572" => "orange3",
						"00FF00" => "gruen1",
						"008B00" => "gruen2",
						"4EEE94" => "gruen3",
						"7CFC00" => "gruen4",
						"BCEE68" => "gruen5",
						"0000FF" => "blau1",
						"98F5FF" => "blau2",
						"BFEFFF" => "blau3",
						"191970" => "blau4",
						"0066FF" => "blau5",
						"FFFF00" => "gelb1",
						"FFD700" => "gelb2",
						"EEE8AA" => "gelb3",
						"EEAD0E" => "gelb4",
						"FFCC33" => "gelb5"
						);

//Linienart, die im Diagramm benutzt werden soll -> min = 1 / max = 109
//die Zehnerstelle gibt den Prozentsatz der Deckkraft der Füllfarbe an
//Bsp.: 73 -> Liniendicke 3 /Graph gefüllt mit 70% Deckkraft -> Füllfarbe ist die Linienfarbe
//NICHT ERLAUBT SIND 0, 10, 20, 30 usw. !!!!
$LINIENKONSTANTEN = array("1"=> "1 Pixel" ,
						"2" => "2 Pixel",
						"3" => "3 Pixel",
						"4" => "4 Pixel",
						"5" => "5 Pixel",
						"11" => "1px gefuellt 10%",
						"21" => "1px gefuellt 20%",
						"51" => "1px gefuellt 50%",
						"71" => "1px gefuellt 60%",
						"81" => "1px gefuellt 80%",
						"22" => "2px gefuellt 20%",
						"42" => "2px gefuellt 40%",
						"62" => "2px gefuellt 60%",
						"82" => "2px gefuellt 80%",);

//Linienart, die standardmäßig benutzt werden soll
$LINIENKONSTANTENSTD = 1;


//Zeitintervalle in denen der Graph neu angelegt werden soll ( in Sekunden)
$NEUERSTELLUNGSINTERVALL = array("0" => "nie autom. erstellen",
								 "180" => "alle 3 Minuten",
								 "300" => "alle 5 Minuten",
								 "600" => "alle 10 Minuten",
								 "900" => "alle 15 Minuten",
								 "1800" => "alle 30 Minuten",
								 "3600" => "jede Stunde",
								 "21600" => "alle 6 Stunden",
								 "43200" => "alle 12 Stunden",
								 "86400" => "alle 24 Stunden",
								 "172800" => "alle 48 Stunden",
								 "604800" => "jede Woche");

//Standardeinstellung für das Zeitintervall in denen der Graph neu angelegt werden soll ( in Sekunden)
$NEUERSTELLUNGSINTERVALLSTD = 86400;

//Zeitintervall das im Graphen dargestellt werden soll( in Sekunden)
$DARSTELLUNGSINTERVALL = array( "60" => "eine Minute",
								"300" => "5 Minuten",
								"600" => "10 Minuten",
								"900" => "15 Minuten",
								"1800" => "30 Minuten",
								"3600" => "eine Stunde",
								"21600" => "6 Stunden",
								"43200" => "12 Stunden",
								"86400" => "24 Stunden",
								"172800" => "48 Stunden",
								"604800" => "7 Tage",
								"1209600" => "14 Tage",
								"2678400" => "31 Tage",
								"15768000" => "6 Monate",
								"31536000" => "12 Monate",
								"63072000" => "24 Monate");

//Standardeinstellung für das Zeitintervall das im Graphen dargestellt werden soll( in Sekunden)
$DARSTELLUNGSINTERVALLSTD = 86400;

//Standardeinstellung für den Offsetwert des Erstellungszeitpunktes ( in Sekunden)
//verschiebt den Zeitpunkt des autom. Erstellens von der vollen Stunden / vollem Tag / voller Woche um die angegebene Zeit in Sek.
//bsp. 3600 -> Graph wird nicht mehr um 2 Uhr nachts erstellt, sonder um 3 Uhr.
$ERSTELLUNGSOFFSETSTD = 0;

//maximale Zeitspanne über die eine Variable gespeichert werden soll
//-> danach werden die alten werte überschrieben.
//Dies sind die Werte für das Auswahlmenü
$VARIABLESPEICHERDAUER = array( "0" => "unbegrenzt",
								"60" => "eine Minute",
								"300" => "5 Minuten",
								"600" => "10 Minuten",
								"900" => "15 Minuten",
								"1800" => "30 Minuten",
								"3600" => "eine Stunde",
								"21600" => "6 Stunden",
								"43200" => "12 Stunden",
								"86400" => "24 Stunden",
								"129600" => "36 Stunden",
								"172800" => "48 Stunden",
								"604800" => "7 Tage",
								"1209600" => "14 Tage",
								"2678400" => "31 Tage",
								"15768000" => "6 Monate",
								"31536000" => "12 Monate",
								"63072000" => "2 Jahre",
								"94608000" => "3 Jahre",
								"157680000" => "5 Jahre",
								"315360000" => "10 Jahre");


//***********************************
//******* Konstanten ab Version 1.5 *****
//***********************************

//Zeitintervall das in einem Graphen zusammengefasst  werden soll( in Sekunden)
$BARGRAPHINTERVALL = 	 array( "30" => "30 Sekunden",
								"60" => "eine Minute",
								"300" => "5 Minuten",
								"600" => "10 Minuten",
								"900" => "15 Minuten",
								"1800" => "30 Minuten",
								"3600" => "eine Stunde",
								"21600" => "6 Stunden",
								"43200" => "12 Stunden",
								"86400" => "24 Stunden",
								"172800" => "48 Stunden",
								"604800" => "7 Tage",
								"1209600" => "14 Tage",
								"2678400" => "31 Tage",
								"15768000" => "6 Monate",
								"31536000" => "12 Monate",
								"63072000" => "24 Monate");

define("BARGRAPH_INTERVALL_STD", 60);

//die Art des neu zu erstellenden Graphen
$GRAPHENTYP = 			array(  "0" => "Liniendiagramm",
								"1" => "Balkendiagramm - Maximum",
								"2" => "Balkendiagramm - Minimum",
								"3" => "Balkendiagramm - Mittelwert",
								"4" => "Balkendiagramm - Summe");
define("GRAPHEN_TYP_STD", 0);


?>

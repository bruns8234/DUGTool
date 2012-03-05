<?
//==================================================================================
// Datei.......: Graphupdate.php
// Beschreibung:  überprüft welche Graph gerade erstellt werden muss
//			wird regelmäßig im Abstand von 60 sek aufgerufen
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 40 $
// zuletzt geändert : 	$Date: 2009-05-29 15:53:44 +0200 (Fr, 29 Mai 2009) $
// Author:			$Author: tobias $
//
// 23.10.2011 TGUSI74
//  + SQLite3 Anpassungen
// 26.10.2011 TGUSI74
//  + Grafikerstellung wird gegen mehrfachen Aufruf verriegelt
//    (Erstellinterval wird ausgesetzt, wird sowieso in 60 Sekunden erneut versucht)
//  + Fehlerbehandlung bei DB oeffnen
//
//==================================================================================

//graphenbasis entält schon das include für sqlitebasis.php
include ("DUGToolbasis.php");
include ($DUGTOOLPFAD."graphenbasis.php");

$DEBUG    = false;
$INFOMSG  = true;
set_time_limit(180);

$SemaphoreNameString = "DUGGRAFIK";
$RandID = mt_rand(100,999);
$ScriptStartTime = getmicrotime();

if (IPS_SemaphoreEnter($SemaphoreNameString,1000))
   {

    $db = new SQLite3($dbpfad, SQLITE3_OPEN_READONLY);
    if ($db === FALSE)
       {
        IPS_LogMessage("DUG DBUpdate ERROR " . $RandID, "Fehler beim oeffnen der DB (eventuell altes DB-Format??? --> nach SQLITE3 migrieren)");
		  IPS_SemaphoreLeave($SemaphoreNameString);
        return;
       }


    $db->busyTimeout(10000);
    if ($db->lastErrorCode() != 0)
       {
        IPS_LogMessage("DUG GraphUpdate ERROR " . $RandID, "Fehler beim DB - Connect Errorcode: " . $db->lastErrorCode() . " - " . $db->lastErrorMsg() );
        $db->close();
		  IPS_SemaphoreLeave($SemaphoreNameString);
		  return;
       }
    //suche alle graphen raus, die ein Erstellungsintervall größer Null haben
    // Null bedeutet der Graph soll nicht automatisch erzeugt  werden
    $select = "SELECT * FROM Graph WHERE ErstellungsIntervall != '0';";
    $graphresult = $db->query($select);

    $i=0;
    while($graphentry = $graphresult->fetchArray())
         {
	       if (isset($graphentry['ID']))
	          {
		        if ($DEBUG)
				     {
				      IPS_LogMessage("DUG GraphUpdate " . $RandID, "Check Graph: ".$graphentry['ID']."--> ".((time()+(int)$graphentry['ErstellungsOffset']) % $graphentry['ErstellungsIntervall']));
					  }
		        if (((time()+(int)$graphentry['ErstellungsOffset']) % $graphentry['ErstellungsIntervall']) < 60)
		           {
			         if ($DEBUG)
						   {
							 IPS_LogMessage("DUG GraphUpdate " . $RandID, "Erstelle Graph: ".$graphentry['ID']);
							}
			         $erstellungsliste[$i] = $graphentry['ID'];
			         $i = $i+1;
		           }
	          }
        }

    $db->close();

    //das Erstellen der Graphen ist deswegen vom  Überprüfen abgekoppelt, da das ERstellen mehrerer Graphen auch mal über 60 sek
    //dauern kann. Die Graphen, die dann nach dieser Zeit überprüft werden, werden dann nicht mehr als ausführbereit erkannt,
    //obwohl sie er wären
    //die Sleep Anweisung wurde eingefügt, um IPS nicht zu lange lahm zu legen
    //ob ein Graph nun eine Sekunde früher oder später erstellt wird ist wohl egal. :)
    if (isset($erstellungsliste))
	    {
	     foreach($erstellungsliste as $graphID)
	        {
             $GraphStartTime = getmicrotime();
			    Graphenerstellen($graphID);

				 if ($INFOMSG)
                {
 	              IPS_LogMessage("DUG GraphUpdate " . $RandID, "Der Graph ".$graphID." wurde neu erstellt --> GraphCreateTime = " . round((getmicrotime() - $GraphStartTime)*1000) . "ms  /  ScriptRunTime = " . round((getmicrotime() - $ScriptStartTime)*1000) . "ms");
					 }
				 sleep(1);
	        }
      }

	IPS_SemaphoreLeave($SemaphoreNameString);
  }
else
  {
   IPS_LogMessage("DUG GraphUpdate " . $RandID, "Updatetimerzyklus wird ausgesetzt weil alter Zyklus noch aktiv");
  }


//*****************************************************************************
function getmicrotime()
	{
    list($usec,$sec)=explode(" ", microtime());
    return ((float)$usec + (float)$sec);
	}
//*****************************************************************************

?>
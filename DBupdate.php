<?
//==================================================================================
// Datei.......: DBupdate.php
// Beschreibung:  speichert Variablenänderungen der Variablen, die dieses Script aufruft in der Datenbank
//
// DUG Version.....: V1.6
// SVN Revisionsnr:	$Revision: 40 $
// zuletzt geändert : 	$Date: 2009-05-29 15:53:44 +0200 (Fr, 29 Mai 2009) $
// Author:			$Author: tobias $
//
// 23.10.2011 TGUSI74
//  + SQLite3 Anpassungen
// 26.10.2011 TGUSI74
//  + SAMEPHORE bei DB-Update eingefuehrt,
//    weil offensichtlich SQLLite3 hier intern ein Problem
//    mit der Verriegelung bei MultiThreading hat
//    ==> es muss jetzt innerhalb von 9 Sekunden moeglich werden
//        den Update durchfuehren zu koennen, sonst werden die Daten verworfen
//  + Fehlerbehandlung bei DB oeffnen
// 29.10.2011 TGUSI74
//  + Jede SQL-Abfrage zusaetzlich mit einer Pruefung versehen
//
//==================================================================================

 unset($_SERVER['argv']);
 unset($_SERVER['argc']);

$logall = true;
$RandID = mt_rand(100,999);
$SemaphoreNameString = "DUGUPDATE";
$ScriptStartTime = getmicrotime();

include ("DUGToolbasis.php");
include ($DUGTOOLPFAD."sqlitebasis.php");


if (IPS_SemaphoreEnter($SemaphoreNameString,9000))
  {
   $ScriptStartSemaphoreRelease = getmicrotime();

   $db = new SQLite3($dbpfad);
   if ($db === FALSE)
      {
       IPS_LogMessage("DUG DBUpdate ERROR " . $RandID, "Fehler beim oeffnen der DB (eventuell altes DB-Format??? --> nach SQLITE3 migrieren)");
       IPS_SemaphoreLeave($SemaphoreNameString);
       return;
      }

   $db->busyTimeout(10000);

   if ($db->lastErrorCode() != 0)
      {
       IPS_LogMessage("DUG DBUpdate ERROR " . $RandID, "Fehler beim DB - Connect Errorcode: " . $db->lastErrorCode() . " - " . $db->lastErrorMsg() );
       $db->close();
       IPS_SemaphoreLeave($SemaphoreNameString);
       return;
      }

   $IPS_VarID = $_IPS["VARIABLE"];

   //Variablentyp abfragen (Boolean, Integery, Float, String)
   $thisvar = IPS_GetVariable($IPS_VarID);
   $varType = $thisvar['VariableValue']['ValueType'];
   if ($varType == 0)
      {
	    $varValue = (int)$_IPS["VALUE"];
      }
   else
      {
	    $varValue = $_IPS["VALUE"];
      }

   //die IPS Variable in der DB suchen
   $select = "SELECT * FROM Variable WHERE IPSID = ".$IPS_VarID;
   $result = $db->query($select);
   if ($result == FALSE)
      {
       IPS_LogMessage("DUG DBUpdate ERROR " . $RandID, "Fehler beim SQL-Statement1 '" . $select . "' --> Script wird abgebrochen !!");
       IPS_SemaphoreLeave($SemaphoreNameString);
       return;
      }
   $varindb = $result->fetchArray();

   //Wenn sie noch nicht in der DB ist, muss sie angelegt werden
   if (!isset($varindb['ID']))
      {
	    //für später mal. evtl aus dem Zusatztext die Einheit extrahieren ( nur ne Idee)
	    $obj = IPS_GetObject($IPS_VarID);
	    $varName = $obj['ObjectName'];
	    $varText = $obj['ObjectInfo'];

       $varEinheit = "unbekannt";
       $varPfad = IPS_GetLocation($IPS_VarID);
	    if ($logall)
		    {
			  IPS_LogMessage("DUG DBUpdate " . $RandID , "Die Variable ".$varName." mit der ID ".$IPS_VarID." ist noch nicht in der DB vorhanden.");
			 }

	   //Variable in der DB neu anlegen
	   //als name der Variable, wird der Pfad mit dem Namen der Variablen eingetragen. Der DB Eintrag Name wird
	   //auch in der Legende des Graphen benutzt.
	   //Der Eintrag Name soll später mal veränderbar sein, während Pfad immer zur Variable gehört
	   $select = "INSERT INTO Variable  (IPSID, Name, Typ, Einheit, MaxIntervallZeit, MaxAnzahl) VALUES ('".$IPS_VarID."','".$varPfad."','".$varType."','".$varEinheit."', 0, 0);";
      $result = $db->query($select);
      if ($result == FALSE)
         {
          IPS_LogMessage("DUG DBUpdate ERROR " . $RandID, "Fehler beim SQL-Statement2 '" . $select . "' --> Script wird abgebrochen !!");
          IPS_SemaphoreLeave($SemaphoreNameString);
          return;
         }

      if ($logall)
            {
             IPS_LogMessage("DUG Tool DB Update " . $RandID , "Die Variable ".$varName." mit der ID ".$IPS_VarID." wurde in der DB '".$dbpfad."' angelegt.");
            }
     }

   //alle Informationen über die Variable aus der DB holen
   $select = "SELECT * FROM Variable WHERE IPSID = ".$IPS_VarID;
   $result = $db->query($select);
   if ($result == FALSE)
      {
       IPS_LogMessage("DUG DBUpdate ERROR " . $RandID, "Fehler beim SQL-Statement3 '" . $select . "' --> Script wird abgebrochen !!");
       IPS_SemaphoreLeave($SemaphoreNameString);
       return;
      }
   $varindb = $result->fetchArray();

   $override = false;
   //falls eine maximale Anzahl an zu speichernden Datensätzen angegeben ist, überprüfe, ob dieses Limit schon erreicht worden ist
   //falls die maximale Anazhal errreicht worden ist, wird override auf true gesetzt
   //dann soll der älteste Wert neu überschrieben werden
   if ((isset($varindb['MaxAnzahl'])) && ($varindb['MaxAnzahl'] != 0))
      {
	    //abfragen wie viele Datensätze dieser Variable schon gespeichert sind
	    $select = "SELECT COUNT(ID) as Anzahl FROM VarEreignis WHERE VarID = '".$varindb['ID']."';";
       $countresult = $db->query($select);
       if ($countresult == FALSE)
          {
           IPS_LogMessage("DUG DBUpdate ERROR " . $RandID, "Fehler beim SQL-Statement4 '" . $select . "' --> Script wird abgebrochen !!");
           IPS_SemaphoreLeave($SemaphoreNameString);
           return;
          }
       $varcount = $countresult->fetchArray();
	    if ($varcount['Anzahl'] >= $varindb['MaxAnzahl'])
		    {
			  $override = true;
			 }
      }

   if (((isset($varindb['MaxIntervallZeit']))&&($varindb['MaxIntervallZeit'] != 0)) || $override)
      {
        //suche den ältesten Datensatz raus
       $select = "SELECT * FROM VarEreignis WHERE VarID = '".$varindb['ID']."' ORDER BY DatumZeit ASC LIMIT 1;";
       $oldvarresult = $db->query($select);
       if ($oldvarresult == FALSE)
          {
           IPS_LogMessage("DUG DBUpdate ERROR " . $RandID, "Fehler beim SQL-Statement5 '" . $select . "' --> Script wird abgebrochen !!");
           IPS_SemaphoreLeave($SemaphoreNameString);
           return;
          }
       $varoldest = $oldvarresult->fetchArray();

        if (($varindb['MaxIntervallZeit'] != 0) && ($varoldest['DatumZeit'] != "") && ($varoldest['DatumZeit'] < (time()-$varindb['MaxIntervallZeit'])))
            {
             $override = true;
            }

         $overrideme = $varoldest;
      }

   if ($override)
      {
        $select = "UPDATE VarEreignis SET 'DatumZeit' = '".time()."', Wert = '".$varValue."' WHERE ID ='".$overrideme['ID']."'; ";
      }
   else
      {
       $select = "INSERT INTO VarEreignis (DatumZeit, Wert, VarID) VALUES (".time().", '".$varValue."','".$varindb['ID']."'); ";
      }

   $result = $db->query($select);

   if ($result == true)
      {
       if ($logall == true)
           {
            IPS_LogMessage("DUG DBUpdate " . $RandID, "Variable ".$IPS_VarID." (".$varindb['ID'].") wurde in der DB '".$dbpfad."' aktualisiert --> ScriptRunTime = " . round((getmicrotime() - $ScriptStartTime)*1000) . "ms (" .round(($ScriptStartSemaphoreRelease - $ScriptStartTime)*1000) . ") / Value = " . $varValue);
           }
      }
   else
      {
        IPS_LogMessage("DUG DBUpdate " . $RandID, "Variable ".$IPS_VarID." (".$varindb['ID'].") konnte in der DB '".$dbpfad."' nicht aktualisiert werden. --> ScriptRunTime = " . round((getmicrotime() - $ScriptStartTime)*1000) . " ms (" .round(($ScriptStartSemaphoreRelease - $ScriptStartTime)*1000) . ")" );
        IPS_LogMessage("DUG DBUpdate " . $RandID, "Statement = '" . $select . "' / LastError: " . $db->lastErrorCode() . " - " . $db->lastErrorMsg());
      }

   $db->close();
   IPS_SemaphoreLeave($SemaphoreNameString);
  }
else
  {
   IPS_LogMessage("DUG DBUpdate " . $RandID, "ERROR: Timeout SemapahoreReservierung '" . $SemaphoreNameString . "'");
  }


//*****************************************************************************
function getmicrotime()
	{
    list($usec,$sec)=explode(" ", microtime());
    return ((float)$usec + (float)$sec);
	}
//*****************************************************************************

?>
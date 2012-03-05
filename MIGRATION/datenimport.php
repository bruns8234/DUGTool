<?php
/* ----------------------------------------------------------------------------
RELEASENOTES:
26.10.2011 TGUSI74
+ Aus CSV-EXPORT in SQLite3 schreiben

---------------------------------------------------------------------------- */

       $filearray = file("EXPORT_VAREREIGNIS.csv");
       $DBName    = "DUGTool.DB";

       set_time_limit(240);

       $CSVTrennzeichen = ";";

 //AB HIER NICHT MEHR ANDERN *************************************************
 //unsauber programmiert :-)


      $db = new SQLite3($DBName);
       if ($db === FALSE)
          {
           print("<H1>FEHLER BEIM OEFFNEN DER DB</H1>");
          }

            $result = $db->query("delete from varereignis");

            if ($result == false)
               {
  	            print("<H6>FEHLER bei loeschen der Datensaetze ind DB</H6>");
  	            die();
  	           }



            $result = $db->query("begin transaction");

            if ($result == false)
               {
  	            print("<H6>FEHLER bei setzen von TRANSACTION</H6>");
  	            die();
  	           }


       $i = 0;

       foreach($filearray as $num => $line)
       {
        $ds = explode($CSVTrennzeichen,$line);
        $text = "";

        if ($i == 0)
          {

           $i = 1;
           $text1 = "INSERT INTO VAREREIGNIS (";

           $recordsheader = count($ds);

           for ($g = 0 ; $g < count($ds)-1; $g++)
              {
               $text1 = $text1 . "" . str_replace("\r\n","",$ds[$g]) . ",";
              }

           $text1 = substr($text1,0,-1);

           $text1 = $text1 . ") values (";


          }
        else
          {
            $text2 = "";


            for ($g = 0 ; $g < $recordsheader-1; $g++)
               {
                $text2 = $text2 . "'" . str_replace("\r\n","",$ds[$g]) . "',";
               }

            $text2 = substr($text2,0,-1);


            $text = $text1 . $text2 .");\r\n";

            $result = $db->query($text);

            if ($result == false)
               {
  	            print("<H6>FEHLER bei " . $text . "</H6>");
  	           }

          }


      }


            $result = $db->query("commit");

            if ($result == false)
               {
  	            print("<H6>FEHLER bei setzen von COMMIT</H6>");
  	            die();
  	           }



$db->close();

$zeit = date("Y.m.d-H:i:s");

print("<H1>FERTIG - $zeit</H1>");

?>
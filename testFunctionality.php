<?php
 /**
  * Plik do testowania zaimplementowanych funkcjonalności
  * - test dodania i usunięcia czujnika
  */

 require 'dodajCzujnik.php';
 require 'usunCzujnik.php';
/*** działa (PDO)***/
//deleteSensor(1);

/*** działa (PDO)***/
//addSensor('1','Test działania PDO');

require 'mailTest.php';
try
{
    sendAlarm(1,1,1,1,true, false, true, false, true);
}

catch (Exception $e)
{
    echo $e->getMessage();
}
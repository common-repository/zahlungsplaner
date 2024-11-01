<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Edit Zahlungsplaner Konfiguration
/****************************************************************************************/


	if ( ! wp_verify_nonce( $_wpnonce, 'zahlungsplan_ueber' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at about_zahlungsplaner.php' ); 
	} 

?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Über den Zahlungsplaner</title>
  </head>
  <div style="background-color: #d6d6d6; font-family:courier;">
  <b>Über den Zahlungsplaner</b>
  <br>
  <br>
  <br>Plane die Termine von Zahlungen (Einnahmen und Ausgaben) und behalte den Überblick über Deine Finanzen.
  <br>
  <br>Dieses Plugin stellt eine Verwaltung von Zahlungen, Einnahmen und Ausgaben, zur Verfügung.
  <br>Die Bestandsdaten können Kategorien zugeordnet werden, um  eine bessere Übersichtlichkeit zu  erhalten.
  <br>Außerdem ist eine  Zuordnung zu Mandanten (WordPress Benutzer) möglich. Damit lassen sich die Bestands-
  <br>daten zwischen den Mandanten trennen.
  <br>Auswertungen, deren  Ergebnisse in  WordPress – Beiträgen  mittels Shortcode berechnet und  dargestellt 
  <br>werden, lassen sich  dann auf einzelne Mandanten beschränken, sodass der Leser dieser Beiträgen nur die
  <br>Daten sieht, die für ihn bestimmt sind.
  <br>Darüber hinaus werden einige  Datumsfunktionen bereitgestellt, die Einfluss auf die Berichte haben. Die
  <br>Datumsfunktionen  können  aber auch  ohne  einen  Bericht genutzt  werden,  bspw. um das aktuelle Datum 
  <br>in einem Beitrag anzuzeigen bzw. Berechnungen damit durchzuführen.
  <br>
  <br>
  <br>Zum<a class="zp_header_link" style="margin-left:8px;" target="_blank" href="http://blog.4bothmann.de/technik/webdesign/wordpress/plugins/">Handbuch</a>
  <br>
  <br>
  <br>Viel Spaß mit Planung Deiner Finanzen.
 
  </div>
</html>







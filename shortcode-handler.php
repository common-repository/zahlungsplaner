<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
global $wpdb;
add_shortcode('zahlungsplaner','zahlungsplaner_display_content');		

function zahlungsplaner_display_content($zahlungsplaner_parameter)
{
	ob_start();
	global $wpdb;
	$atts = array();

	if(is_array($zahlungsplaner_parameter))
	{	
		$bericht_name = "";
		$zp_name = shortcode_atts( $zahlungsplaner_parameter, $atts )[0];	
		/* nur Datums-Rechnungen und Ausgaben... */
		if ( zp_left( $zp_name, 1 ) == "{" )
		{
			zp_run( $zahlungsplaner_parameter, 0, "", "", 0, 0 );
			return ob_get_clean();
		}		
		if ( count($zahlungsplaner_parameter) > 1 ) 
		{
			$bericht_name = shortcode_atts( $zahlungsplaner_parameter, $atts )[1];
			unset( $zahlungsplaner_parameter[0] );
			$zahlungsplaner_parameter = array_values($zahlungsplaner_parameter);
		}
		$zp_name = esc_sql( $zp_name );
		$inhalt = $wpdb->get_col("SELECT inhalt FROM ".$wpdb->prefix."zahlungsplan_bericht WHERE bericht='".$zp_name."' AND stac=1 LIMIT 0,1",0,"inhalt"); 
		if ( !$inhalt )
		{
			echo "Zahlungsplaner: Bericht : '$zp_name' nicht gefunden";
			return ob_get_clean();
		}
		$id = $wpdb->get_col("SELECT id FROM ".$wpdb->prefix."zahlungsplan_bericht WHERE bericht='".$zp_name."' AND stac=1 LIMIT 0,1",0); 
		if ( !$id )
			return;
		$id = $id[0];

		/* Parameter aus Array erzeugen... */  
		$bericht_abfrage = unserialize(base64_decode($inhalt[0]));
		zp_run( $zahlungsplaner_parameter, $id, $bericht_abfrage['mandant'], $bericht_abfrage['art'], $bericht_abfrage['ea'], $bericht_abfrage['zeitraumvon'], $bericht_abfrage['zeitraumbis']);
		return ob_get_clean();
	}
	else 
	{
		echo "Zahlungsplaner: Aufruf ohne Parameter";
		return ob_get_clean();
	}
}

?>

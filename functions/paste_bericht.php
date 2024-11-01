<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Kopieren Zahlungsplaner Bericht
/****************************************************************************************/
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bericht-paste' ) )
	{
 	   // This nonce is not valid.
 	   die( 'Security check at paste_bericht.php' );
	}
	$pbid = (int) sanitize_key( $_GET['id'] );
		
  	global $wpdb;
	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
	$pbid = esc_sql( $pbid );
  	$pbres = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan_bericht WHERE id='.$pbid.' LIMIT 0,1',ARRAY_A);
	$pbres[0]['id'] = "NULL";
	if ( strlen( $pbres[0]['bericht'] ) < 244 ) $pbres[0]['bericht'] .= "-Kopie";
	else $pbres[0]['bericht'] = substr( $pbres[0]['bericht'], 0, 243 )."-Kopie";
	$pbres = esc_sql( $pbres );
	while ( $wpdb->get_results('SELECT bericht FROM '.$wpdb->prefix.'zahlungsplan_bericht WHERE bericht="'.$pbres[0]['bericht'].'" LIMIT 0,1',ARRAY_A) )
	{
		if ( strlen( $pbres[0]['bericht'] ) < 244 ) $pbres[0]['bericht'] .= "-Kopie";
		else return;
		$pbres = esc_sql( $pbres );
	} 
 	$wpdb->insert($wpdb->prefix.'zahlungsplan_bericht',$pbres[0]);
	header("Location:".admin_url('admin.php?page=bericht_list_bestand&last_message=3'));
?>

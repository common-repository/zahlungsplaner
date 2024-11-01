<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Kopieren Zahlungsplaner Bestand
/****************************************************************************************/
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'zahlungsplan-paste' ) )
	{
 	   // This nonce is not valid.
 	   die( 'Security check at paste_bestand.php' );
	}
	$pbid = (int) sanitize_key( $_GET['id'] );
		
  	global $wpdb;
	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
	$pbid = esc_sql( $pbid );
  	$pbres = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE id='.$pbid.' LIMIT 0,1',ARRAY_A);
	$pbres[0]['id'] = "NULL";
	if ( strlen( $pbres[0]['bezeichnung'] ) < 244 ) $pbres[0]['bezeichnung'] .= "-Kopie";
	else $pbres[0]['bezeichnung'] = substr( $pbres[0]['bezeichnung'], 0, 243 )."-Kopie";
	$pbres = esc_sql( $pbres );
	while ( $wpdb->get_results('SELECT bezeichnung FROM '.$wpdb->prefix.'zahlungsplan WHERE bezeichnung="'.$pbres[0]['bezeichnung'].'" LIMIT 0,1',ARRAY_A) )
	{
		if ( strlen( $pbres[0]['bezeichnung'] ) < 244 ) $pbres[0]['bezeichnung'] .= "-Kopie";
		else return;
		$pbres = esc_sql( $pbres );
	} 
 	$wpdb->insert($wpdb->prefix.'zahlungsplan',$pbres[0]);
	header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&last_message=3'));
?>

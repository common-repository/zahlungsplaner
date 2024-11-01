<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Kopieren Zahlungsplaner Kategorie
/****************************************************************************************/
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'kategorie-paste' ) )
	{
 	   // This nonce is not valid.
 	   die( 'Security check at paste_kategorie.php' );
	}

	$pbid = (int) sanitize_key( $_GET['id'] );

  	global $wpdb;

	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
	$pbid = esc_sql( $pbid );
  	$pbres = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE id='.$pbid.' LIMIT 0,1',ARRAY_A);
	$pbres[0]['id'] = "NULL";
	if ( strlen( $pbres[0]['kategorie'] ) < 244 ) $pbres[0]['kategorie'] .= "-Kopie";
	else $pbres[0]['kategorie'] = substr( $pbres[0]['kategorie'], 0, 243 )."-Kopie";
	$pbres = esc_sql( $pbres );
	$res = $wpdb->get_results('SELECT kategorie FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE kategorie="'.$pbres[0]['kategorie'].'" LIMIT 0,1',ARRAY_A);
	while ( $wpdb->get_results('SELECT kategorie FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE kategorie="'.$pbres[0]['kategorie'].'" LIMIT 0,1',ARRAY_A) )
	{
		if ( strlen( $pbres[0]['kategorie'] ) < 244 ) $pbres[0]['kategorie'] .= "-Kopie";
		else return;
		$pbres = esc_sql( $pbres );
	}
 	$wpdb->insert($wpdb->prefix.'zahlungsplan_kategorie',$pbres[0]);
	header("Location:".admin_url('admin.php?page=kategorie_list_bestand&last_message=3'));
?>

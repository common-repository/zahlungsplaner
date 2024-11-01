<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Pause Zahlungsplaner Bericht
/****************************************************************************************/
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bericht-pause' ) )
	{
 	   // This nonce is not valid.
 	   die( 'Security check at pause_bericht.php' );
	}
	$pbid = (int) sanitize_key( $_GET['id'] );
	$pbstac = (int) sanitize_key( $_GET['status'] );
	$pagenum = (int) sanitize_key($_GET['pageno'] );
		
  	global $wpdb;
	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
	$pbid = esc_sql( $pbid );
  	$pbres = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan_bericht WHERE id='.$pbid.' LIMIT 0,1',ARRAY_A);
 
  	if (empty($pbres))
  	{
  		echo "SATZ";
		return;
  	}
  	
  	if ( $pbres[0]['stac'] != $pbstac )
  	{	
  		echo "WERT";
		return;
  	} 
	$updated = $wpdb->update($wpdb->prefix.'zahlungsplan_bericht', array('stac'=>($pbres[0]['stac']==0?1:0)), array('id'=>$pbid));
  	if ( false === $updated )
  	{
  		echo "UPDATE";
		return;
  	}   			
	header("Location:".admin_url('admin.php?page=bericht_list_bestand&pageno='.$pagenum));
?>

<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) die();

delete_option( "zahlungsplaner_db_version" );
delete_site_option( "zahlungsplaner_db_version" );
	
global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix.'zahlungsplan');
$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix.'zahlungsplan_konfig');
$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix.'zahlungsplan_kategorie');
$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix.'zahlungsplan_bericht');

?>

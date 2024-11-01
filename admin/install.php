<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $zahlungsplaner_db_version;
$zahlungsplaner_db_version = '1.0.0';

function zp_zahlungsplaner_install() {
	global $wpdb; 
	global $zahlungsplaner_db_version;

	if (get_site_option( "zahlungsplaner_db_version" ) != "" )
		delete_site_option( "zahlungsplaner_db_version" );
	
	$zahlungsplan = $wpdb->prefix.'zahlungsplan';
	$zahlungsplan_konfig = $wpdb->prefix.'zahlungsplan_konfig';
	$zahlungsplan_kategorie = $wpdb->prefix.'zahlungsplan_kategorie';
	$zahlungsplan_bericht = $wpdb->prefix.'zahlungsplan_bericht';

	if(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} else {
		die('Probleme beim Öffnen von \'/wp-admin/upgrade-functions.php\' bzw. \'/wp-admin/includes/upgrade.php\'');
	}
	
	$charset_collate = $wpdb->get_charset_collate();

	$create_table = array();
	$create_table['zahlungsplan'] = "CREATE TABLE $zahlungsplan (
  		`id` bigint(11) NOT NULL AUTO_INCREMENT,
  		`mandant_id` bigint(11) NOT NULL,
  		`bezeichnung` varchar(250) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL DEFAULT '',
  		`beschreibung` text CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  		`kategorie_id` bigint(11) NOT NULL,
  		`ea` int(11) NOT NULL,
  		`betrag` double NOT NULL,
  		`umbuchung` int(11) NOT NULL,
  		`start` date NOT NULL,
  		`ende` date NOT NULL,
  		`turnus` int(11) NOT NULL,
  		`anzahl` int(11) NOT NULL,
  		`stac` int(11) NOT NULL,
  		`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  		UNIQUE KEY `id` (`id`),
  		KEY `bezeichnung` (`bezeichnung`),
  		KEY `mandant` (`mandant_id`),
  		KEY `kategorie` (`kategorie_id`)) ".$charset_collate.";";
	$create_table['zahlungsplan_konfig'] = "CREATE TABLE $zahlungsplan_konfig (
  		`id` bigint(11) NOT NULL AUTO_INCREMENT,
  		`inhalt` longtext CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  		`stac` int(11) NOT NULL,
  		`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  		UNIQUE KEY `id` (`id`)) ".$charset_collate.";";
	$create_table['zahlungsplan_kategorie'] = "CREATE TABLE $zahlungsplan_kategorie (
  		`id` bigint(11) NOT NULL AUTO_INCREMENT,
  		`kategorie` varchar(250) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL DEFAULT '',
  		`stac` int(11) NOT NULL,
  		`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  		UNIQUE KEY `id` (`id`),
  		KEY `kategorie` (`kategorie`)) ".$charset_collate.";";
	$create_table['zahlungsplan_bericht'] = "CREATE TABLE $zahlungsplan_bericht (
  		`id` bigint(11) NOT NULL AUTO_INCREMENT,
  		`bericht` varchar(250) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL DEFAULT '',
  		`beschreibung` text CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  		`inhalt` longtext CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  		`stac` int(11) NOT NULL,
  		`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  		UNIQUE KEY `id` (`id`),
  		KEY `bericht` (`bericht`)) ".$charset_collate.";";

	dbDelta( $create_table['zahlungsplan'] );
	dbDelta( $create_table['zahlungsplan_konfig'] );
	dbDelta( $create_table['zahlungsplan_kategorie'] );
	dbDelta( $create_table['zahlungsplan_bericht'] );	
	add_site_option( 'zahlungsplaner_db_version', $zahlungsplaner_db_version );
}

function zp_zahlungsplaner_install_data() {
	global $wpdb;

	// War es Install oder Upgrade
	$table_name = $wpdb->prefix.'zahlungsplan';
	$table_name = esc_sql( $table_name );
	$first_zahlungsplan = $wpdb->get_var( "SELECT id FROM $table_name LIMIT 1" );
	// Wenn Install, Mustersätze erzeugen
	if ( empty( $first_zahlungsplan ) ) 
	{
		$wpdb->insert( 
			$table_name, 		
			array( 
				'mandant_id' => get_current_user_id(), 
				'bezeichnung' => "Gehalt", 
				'beschreibung' => "Vom Arbeitgeber",
				'kategorie_id' => "1",		
				'ea' => "1",
				'betrag' => "2000",
				'umbuchung' => "0",
				'start' => "2018-01-01",
				'ende' => "2019-12-31",
				'turnus' => "1",
				'anzahl' => "1",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 
			array( 
				'mandant_id' => get_current_user_id(), 
				'bezeichnung' => "Kindergeld", 
				'beschreibung' => "Tochter",
				'kategorie_id' => "1",		
				'ea' => "1",
				'betrag' => "200",
				'umbuchung' => "0",
				'start' => "2018-01-01",
				'ende' => "2019-12-31",
				'turnus' => "1",
				'anzahl' => "1",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 	
			array( 
				'mandant_id' => get_current_user_id(), 
				'bezeichnung' => "Miete", 
				'beschreibung' => "Wohnung",
				'kategorie_id' => "2",		
				'ea' => "0",
				'betrag' => "1200",
				'umbuchung' => "0",
				'start' => "2018-01-01",
				'ende' => "2019-12-31",
				'turnus' => "1",
				'anzahl' => "1",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 	
			array( 
				'mandant_id' => get_current_user_id(), 
				'bezeichnung' => "KFZ-Steuern", 
				'beschreibung' => "Familienauto",
				'kategorie_id' => "3",		
				'ea' => "0",
				'betrag' => "300",
				'umbuchung' => "0",
				'start' => "2018-04-01",
				'ende' => "2019-12-31",
				'turnus' => "12",
				'anzahl' => "1",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 
			array( 
				'mandant_id' => get_current_user_id(), 
				'bezeichnung' => "Haftplicht", 
				'beschreibung' => "private Haftplicht",
				'kategorie_id' => "4",		
				'ea' => "0",
				'betrag' => "100",
				'umbuchung' => "0",
				'start' => "2018-06-01",
				'ende' => "2019-12-31",
				'turnus' => "12",
				'anzahl' => "1",
				'stac' => "1"
			));			
		$wpdb->insert( 
			$table_name, 
			array( 
				'mandant_id' => get_current_user_id(), 
				'bezeichnung' => "KFZ", 
				'beschreibung' => "Versicherung Familienauto",
				'kategorie_id' => "4",		
				'ea' => "0",
				'betrag' => "50",
				'umbuchung' => "0",
				'start' => "2018-01-01",
				'ende' => "2019-12-31",
				'turnus' => "1",
				'anzahl' => "1",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 
			array( 
				'mandant_id' => get_current_user_id(), 
				'bezeichnung' => "Telefon Internet", 
				'beschreibung' => "Telefon Internet Deutsche Telekom",
				'kategorie_id' => "2",		
				'ea' => "0",
				'betrag' => "50",
				'umbuchung' => "0",
				'start' => "2018-01-01",
				'ende' => "2019-12-31",
				'turnus' => "1",
				'anzahl' => "1",
				'stac' => "1"
			));    
	}
	// War es Install oder Upgrade
	$table_name = $wpdb->prefix.'zahlungsplan_konfig';
	$table_name = esc_sql( $table_name );
	$first_konfig = $wpdb->get_var( "SELECT id FROM $table_name LIMIT 1" );
	// Wenn Install, Mustersätze erzeugen
	if ( empty( $first_konfig ) ) 
	{	
		$wpdb->insert( 
			$table_name, 
			array( 
				'inhalt' => "YTo5OntzOjExOiJ0YWJlbGxpZXJlbiI7czoxOiIxIjtzOjk6InRhYmZhcmJlMSI7czo2OiJEREREREQiO3M6OToidGFiZmFyYmUyIjtzOjY6IkU4RkRFNyI7czo4OiJwcmludGthdCI7czoxOiIxIjtzOjk6InByaW50c2lnbiI7czoxOiIxIjtzOjg6Im1heGxpbmVzIjtzOjI6IjEwIjtzOjEzOiJmaWx0ZXJiZXN0YW5kIjtzOjA6IiI7czoxNToiZmlsdGVya2F0ZWdvcmllIjtzOjA6IiI7czoxMzoiZmlsdGVyYmVyaWNodCI7czowOiIiO30=",
				'stac' => "1"
			));	
	}
	// War es Install oder Upgrade
	$table_name = $wpdb->prefix.'zahlungsplan_kategorie';
	$table_name = esc_sql( $table_name );
	$first_kategorie = $wpdb->get_var( "SELECT id FROM $table_name LIMIT 1" );
	// Wenn Install, Mustersätze erzeugen
	if ( empty( $first_kategorie ) ) 
	{
		$wpdb->insert( 
			$table_name, 
			array( 
				'kategorie' => "Einkommen",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 	
			array( 
				'kategorie' => "Wohnen",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 
			array( 
				'kategorie' => "Auto",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 
			array( 
				'kategorie' => "Versicherung",
				'stac' => "1"
			));
	}
	// War es Install oder Upgrade
	$table_name = $wpdb->prefix.'zahlungsplan_bericht';
	$table_name = esc_sql( $table_name );
	$first_bericht = $wpdb->get_var( "SELECT id FROM $table_name LIMIT 1" );
	// Wenn Install, Mustersätze erzeugen
	if ( empty( $first_bericht ) ) 
	{
		$wpdb->insert( 
			$table_name, 
			array( 
				'bericht' => "Gesamt-Listenform-wie-Auszug-mit-CSV-Download", 
				'beschreibung' => "Liste alle Kategorien, alle Mandanten, alle Zeiträume, Einnahmen und Ausgaben, tabellarisch. Wie Kontoauszug, mit CSV Download.",
				'inhalt' => "YTo2OntzOjc6Im1hbmRhbnQiO3M6MToiMCI7czoxMToiemVpdHJhdW12b24iO3M6MTA6IjAwMDAtMDAtMDAiO3M6MTE6InplaXRyYXVtYmlzIjtzOjEwOiIwMDAwLTAwLTAwIjtzOjM6ImFydCI7czoxOiI0IjtzOjI6ImVhIjtzOjE6IjEiO3M6MTM6ImthdGVnb3JpZWxpc3QiO3M6MjoiLTEiO30=",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 
			array( 
				'bericht' => "Versicherungen-gesamt", 
				'beschreibung' => "Alle Versicherungen über den gesamten Zeitraum.",
				'inhalt' => "YTo2OntzOjc6Im1hbmRhbnQiO3M6MToiMCI7czoxMToiemVpdHJhdW12b24iO3M6MTA6IjAwMDAtMDAtMDAiO3M6MTE6InplaXRyYXVtYmlzIjtzOjEwOiIwMDAwLTAwLTAwIjtzOjM6ImFydCI7czoxOiIxIjtzOjI6ImVhIjtzOjE6IjEiO3M6MTM6ImthdGVnb3JpZWxpc3QiO3M6MToiNCI7fQ==",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 
			array( 
				'bericht' => "Tabellarisch-wie-Karteikarten", 
				'beschreibung' => "Tabellen alle Kategorien, alle Mandanten, alle Zeiträume, Einnahmen und Ausgaben. Wie Karteikarten.",
				'inhalt' => "YTo2OntzOjc6Im1hbmRhbnQiO3M6MToiMCI7czoxMToiemVpdHJhdW12b24iO3M6MTA6IjAwMDAtMDAtMDAiO3M6MTE6InplaXRyYXVtYmlzIjtzOjEwOiIwMDAwLTAwLTAwIjtzOjM6ImFydCI7czoxOiIyIjtzOjI6ImVhIjtzOjE6IjEiO3M6MTM6ImthdGVnb3JpZWxpc3QiO3M6MjoiLTEiO30=",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 
			array( 
				'bericht' => "Gesamt-Listenform-wie-Auszug-monatlich-saldiert", 
				'beschreibung' => "Liste alle Kategorien, alle Mandanten, alle Zeiträume, Einnahmen und Ausgaben, tabellarisch. Wie Kontoauszug, monatlich saldiert.",
				'inhalt' => "YTo2OntzOjc6Im1hbmRhbnQiO3M6MToiMCI7czoxMToiemVpdHJhdW12b24iO3M6MTA6IjAwMDAtMDAtMDAiO3M6MTE6InplaXRyYXVtYmlzIjtzOjEwOiIwMDAwLTAwLTAwIjtzOjM6ImFydCI7czoxOiI2IjtzOjI6ImVhIjtzOjE6IjEiO3M6MTM6ImthdGVnb3JpZWxpc3QiO3M6MjoiLTEiO30=",
				'stac' => "1"
			));
		$wpdb->insert( 
			$table_name, 
			array( 
				'bericht' => "Summen-je-Kategorie", 
				'beschreibung' => "Eine Zeile je Kategorie mit Gesamtsumme.",
				'inhalt' => "YTo2OntzOjc6Im1hbmRhbnQiO3M6MToiMCI7czoxMToiemVpdHJhdW12b24iO3M6MTA6IjAwMDAtMDAtMDAiO3M6MTE6InplaXRyYXVtYmlzIjtzOjEwOiIwMDAwLTAwLTAwIjtzOjM6ImFydCI7czoxOiIzIjtzOjI6ImVhIjtzOjE6IjEiO3M6MTM6ImthdGVnb3JpZWxpc3QiO3M6MjoiLTEiO30=",
				'stac' => "1"
			));
	}	
}

/*****************************************************************************************
/* Upgrade Function...
/****************************************************************************************/

function zp_zahlungsplaner_update_db_check() 
{
    global $zahlungsplaner_db_version;
    if ( get_site_option( 'zahlungsplaner_db_version' ) != $zahlungsplaner_db_version ) 
	{
        zp_zahlungsplaner_install();
		zp_zahlungsplaner_install_data();
    }
}
update_site_option( "zahlungsplaner_db_version", "1.0" );
register_activation_hook( __FILE__, 'zp_zahlungsplaner_install' );
register_activation_hook( __FILE__, 'zp_zahlungsplaner_install_data' );
add_site_option( "zahlungsplaner_db_version", "1.0.0" );
add_action( 'plugins_loaded', 'zp_zahlungsplaner_update_db_check' );

?>

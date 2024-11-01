<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Create Menu ..
/****************************************************************************************/

add_action('admin_menu', 'zp_zahlungsplan_menu');

function zp_zahlungsplan_menu(){

	add_menu_page('zahlungsplan', 'Zahlungsplan', 'manage_options', 'zahlungsplan-bestand-manage','zp_zahlungsplan_bestand',plugins_url('zahlungsplaner/images/logo-bw.png'));
	add_submenu_page('zahlungsplan-bestand-manage', 'Kategorien bearbeiten', 'Kategorien', 'manage_options', 'kategorie_list_bestand','zp_kategorie_list_bestand');
	add_submenu_page('zahlungsplan-bestand-manage', 'Zahlungsplan Abfragen', 'Berichte', 'manage_options', 'bericht_list_bestand','zp_bericht_list_bestand');
	add_submenu_page('zahlungsplan-bestand-manage', 'Zahlungsplan Einstellungen', 'Einstellungen', 'manage_options', 'zahlungsplan_einstellungen','zp_zahlungsplan_einstellungen');
	add_submenu_page('zahlungsplan-bestand-manage', 'Über Zahlungsplan', 'Über Zahlungsplan', 'manage_options', 'zahlungsplan_ueber','zp_zahlungsplan_ueber');	

}

function zp_kategorie_list_bestand()
{
	require( dirname( __FILE__ ) . '/header.php' );
	require( dirname( __FILE__ , 2 ) . '/functions/list_kategorie.php');	
	require( dirname( __FILE__ ) . '/footer.php' );	
}

function zp_bericht_list_bestand()
{
	require( dirname( __FILE__ ) . '/header.php' );
	require( dirname( __FILE__ , 2 ) . '/functions/list_bericht.php');
	require( dirname( __FILE__ ) . '/footer.php' );		
}

function zp_zahlungsplan_einstellungen()
{
	require( dirname( __FILE__ ) . '/header.php' );
	$_wpnonce = wp_create_nonce('zahlungsplan_einstellungen');
	require( dirname( __FILE__ , 2 ) . '/functions/edit_config.php');
	require( dirname( __FILE__ ) . '/footer.php' );	
}

function zp_zahlungsplan_ueber(){
	require( dirname( __FILE__ ) . '/header.php' );		
	$_wpnonce = wp_create_nonce('zahlungsplan_ueber');
	require( dirname( __FILE__ , 2 ) . '/functions/about_zahlungsplaner.php');
	require( dirname( __FILE__ ) . '/footer.php' );
}

function zp_zahlungsplan_bestand()
{
	/*
		$formflag :
			0 = executiere list_bestand.php
			1 = exit
			2 = executiere list_kategorie.php
			3 = executiere list_bricht.php
	*/
	
	/* 
	ZAHLUNGSPLAN 
	*/
	$formflag = 0;
	if(isset($_GET['action']) && $_GET['action']=='zahlungsplan-insert' )
	{
		//echo "! insert_bestand";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/insert_bestand.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 1;
	}
	if(isset($_GET['action']) && $_GET['action']=='zahlungsplan-edit' )
	{
		//echo "! edit_bestand";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/edit_bestand.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 1;
	}
	if(isset($_GET['action']) && $_GET['action']=='zahlungsplan-paste' )
	{
		//echo "! paste_bestand";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/paste_bestand.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 0;
	}
	if(isset($_GET['action']) && $_GET['action']=='zahlungsplan-delete' )
	{
		//echo "! delete_bestand";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/delete_bestand.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 1;	
	}		
	if(isset($_GET['action']) && $_GET['action']=='zahlungsplan-pause' )
	{
		//echo "! pause_bestand";	
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/pause_bestand.php');	
		require( dirname( __FILE__ ) . '/footer.php' );	
		$formflag = 0;	// nach dem Update Bestand wieder anzeigen
	}
	/* 
	KATEGORIE 
	*/	
	if(isset($_GET['action']) && $_GET['action']=='kategorie-insert' )
	{
		//echo "! insert_kategorie";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/insert_kategorie.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 1;
	}
	if(isset($_GET['action']) && $_GET['action']=='kategorie-edit' )
	{
		//echo "! edit_kategorie";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/edit_kategorie.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 1;
	}
	if(isset($_GET['action']) && $_GET['action']=='kategorie-paste' )
	{
		//echo "! paste_kategorie";	
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/paste_kategorie.php');	
		require( dirname( __FILE__ ) . '/footer.php' );	
		$formflag = 2;	// nach dem Kopieren Bestand wieder anzeigen
	}
	if(isset($_GET['action']) && $_GET['action']=='kategorie-delete' )
	{
		//echo "! delete_kategorie";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/delete_kategorie.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 1;
	}	
	if(isset($_GET['action']) && $_GET['action']=='kategorie-pause' )
	{
		//echo "! pause_kategorie";	
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/pause_kategorie.php');	
		require( dirname( __FILE__ ) . '/footer.php' );	
		$formflag = 2;	// nach dem Update Bestand wieder anzeigen
	}
	/* 
	BERICHT 
	*/	
	if(isset($_GET['action']) && $_GET['action']=='bericht-insert' )
	{
		//echo "! insert_kategorie";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/insert_bericht.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 1;
	}
	if(isset($_GET['action']) && $_GET['action']=='bericht-edit' )
	{
		//echo "! edit_kategorie";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/edit_bericht.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 1;
	}
	if(isset($_GET['action']) && $_GET['action']=='bericht-paste' )
	{
		//echo "! paste_bericht";	
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/paste_bericht.php');	
		require( dirname( __FILE__ ) . '/footer.php' );	
		$formflag = 3;	// nach dem Update Bestand wieder anzeigen
	}
	if(isset($_GET['action']) && $_GET['action']=='bericht-delete' )
	{
		//echo "! delete_bericht";
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/delete_bericht.php');
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 1;
	}	
	if(isset($_GET['action']) && $_GET['action']=='bericht-pause' )
	{
		//echo "! pause_bericht";	
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/pause_bericht.php');		
		require( dirname( __FILE__ ) . '/footer.php' );
		$formflag = 3;	// nach dem Update Bestand wieder anzeigen
	}
	/* 
	DEFAULT=LIST 
	*/
	if($formflag == 0){
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/list_bestand.php' );	
		require( dirname( __FILE__ ) . '/footer.php' );
	}
	if($formflag == 2){
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/list_kategorie.php' );		 
		require( dirname( __FILE__ ) . '/footer.php' );
	}
	if($formflag == 3){
		require( dirname( __FILE__ ) . '/header.php' );
		require( dirname( __FILE__ , 2 ) . '/functions/list_bericht.php' );	
		require( dirname( __FILE__ ) . '/footer.php' );
	}
}

function zp_zahlungsplan_add_style_script(){
	wp_register_style('xyz_ips_style', plugins_url('zahlungsplan/css/zahlungsplan_styles.css'));
	wp_enqueue_style('zahlungsplan_style');
}
add_action('admin_enqueue_scripts', 'zp_zahlungsplan_add_style_script');

?>
<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
Plugin Name: Zahlungsplaner
Plugin URI: http://blog.4bothmann.de/technik/webdesign/wordpress/plugins/
Description: Plane die Termine von Zahlungen (Einnahmen und Ausgaben) und behalte den Überblick über Deine Finanzen.
Version: 1.3.1
Author: Jörg Bothmann
Author URI: http://blog.4bothmann.de/
Update Server: https://blog.4bothmann.de/wp-content/download/wp/
Min WP Version: 4.6
Max WP Version: 5.4.1
*/
/*****************************************************************************************
/* MAIN...
/****************************************************************************************/

require( dirname( __FILE__ ) . '/admin/install.php' );
require( dirname( __FILE__ ) . '/admin/menu.php' );
require( dirname( __FILE__ ) . '/functions/functions.php' );
require( dirname( __FILE__ ) . '/shortcode-handler.php' );

/*****************************************************************************************
/* Quellen :
/*   Icons : https://www.iconfinder.com/icons/71017
/****************************************************************************************/


?>

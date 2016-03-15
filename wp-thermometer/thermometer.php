<?php
/**
 * Plugin Name: Wordpress Thermometer
 * Plugin URI: https://github.com/mikifus/wp-thermometer
 * Description: Displays a horizontal "thermometer" with a task's progress
 * Version: 0.5
 * Original Author: Dan Conley
 * Original Author URI: http://www.danconley.net
 * Current maintainer: Mikifus
 * Text Domain: wp-thermometer
 * Domain Path: /lang/
 *
 * License: Kopyleft
*/

require_once('activation.php');


// the admin panel stuff is found here
require('thermometer_admin.php');
require('thermometer_admin_table.php');
//require('thermometer_widget.php');

// Initialize main class
add_action( 'plugins_loaded', function () {
    // Translations
    load_plugin_textdomain( 'wp-thermometer', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	Wp_Thermometer_Plugin::get_instance();

} );
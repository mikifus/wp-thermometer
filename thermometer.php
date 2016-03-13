<?php
/*
Plugin Name: Wordpress Thermometer
Plugin URI: https://github.com/mikifus/wp-thermometer
Description: Displays a horizontal "thermometer" with a task's progress
Version: 0.5
Original Author: Dan Conley
Original Author URI: http://www.danconley.net
Current maintainer: Mikifus
License: Kopyleft
*/

require_once('activation.php');

function include_thermometer_css() {
	echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"" . plugins_url() . "/wp-thermometer/thermometer.css\" />";
}

add_action('wp_head', 'include_thermometer_css');

// the admin panel stuff is found here
require('thermometer_admin.php');
require('thermometer_admin_table.php');
require('thermometer_widget.php');

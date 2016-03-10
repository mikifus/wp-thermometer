<?php
function wp_thermometer_setup() {
    $jal_db_version = '1.0';
    
    // Custom db table
    $wpdb = $GLOBALS['wpdb'];
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'thermometers';
    if ($wpdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name || $jal_db_version !== get_option('jal_db_version') ) // Always check if it exists or needs update
    {
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name."(
            -- Primary key
            id            BIGINT NOT NULL AUTO_INCREMENT,

            title          VARCHAR(200) NOT NULL,
            subtitle       VARCHAR(200),
            description    TEXT,

            goal           INTEGER     NOT NULL,
            current        INTEGER     NOT NULL,

            deadline       DATETIME    NOT NULL,
            options        TEXT,

            created        DATETIME    NOT NULL,
            updated        DATETIME    NOT NULL,

            PRIMARY KEY  (id)
        ) ".$charset_collate.';';

        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option( 'jal_db_version', $jal_db_version );
    }
}
add_action( 'init', 'wp_thermometer_setup' );

function wp_thermometer_install() {
}
register_activation_hook( __FILE__, 'wp_thermometer_install' );

function wp_thermometer_uninstall() {
    // If uninstall is not called from WordPress, exit
    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
        exit();
    }

    $option_name = 'wp-thermometer';

    delete_option( $option_name );

    // For site options in Multisite
    delete_site_option( $option_name );

    // Drop a custom db table
    global $wpdb;
    $table_name = $wpdb->prefix . 'thermometers';
    $wpdb->query( "DROP TABLE IF EXISTS " . $table_name );
}

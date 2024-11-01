<?php

/* if uninstall.php is not called by WordPress, die */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

/* Drop database */
global $wpdb;
$wpdb->query('DROP TABLE IF EXISTS '. $wpdb->prefix . 'wpcdnkoloss_files');

?>

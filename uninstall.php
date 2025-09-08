<?php
/**
 * Uninstall Gemini SEO Plugin
 * Cleans up plugin data on uninstall.
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Delete plugin options
delete_option('gemini_seo_settings');
delete_option('gemini_seo_redirects');
// Add more options as needed

// Drop custom tables if created
// global $wpdb;
// $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gemini_seo_redirects");
// $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gemini_seo_404_logs");

// Delete post meta
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_gemini_seo_%'");

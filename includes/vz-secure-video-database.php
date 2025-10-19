<?php
/**
 * Database Functions
 * 
 * Handles database table creation for view tracking
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create custom database tables for view tracking
 */
function vz_secure_video_create_view_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // View log table - stores every view
    $table_log = $wpdb->prefix . 'vz_video_view_log';
    $sql_log = "CREATE TABLE $table_log (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id bigint(20) UNSIGNED NOT NULL,
        user_id bigint(20) UNSIGNED DEFAULT 0,
        ip_address varchar(45) DEFAULT NULL,
        user_agent text,
        viewed_at datetime DEFAULT CURRENT_TIMESTAMP,
        view_duration int(11) DEFAULT NULL,
        PRIMARY KEY (id),
        KEY post_id (post_id),
        KEY user_id (user_id),
        KEY viewed_at (viewed_at),
        KEY post_user (post_id, user_id)
    ) $charset_collate;";
    
    // View cache table - stores pre-calculated counts for performance
    $table_cache = $wpdb->prefix . 'vz_video_view_cache';
    $sql_cache = "CREATE TABLE $table_cache (
        post_id bigint(20) UNSIGNED NOT NULL,
        total_views int(11) DEFAULT 0,
        unique_views int(11) DEFAULT 0,
        last_calculated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (post_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_log);
    dbDelta($sql_cache);
}

/**
 * Update database version
 */
function vz_secure_video_update_view_db_version() {
    update_option('vz_secure_video_view_db_version', '1.0.0');
}

// Hook into activation
register_activation_hook(VZ_SECURE_VIDEO_PLUGIN_DIR . 'vz-secure-video.php', 'vz_secure_video_create_view_tables');
register_activation_hook(VZ_SECURE_VIDEO_PLUGIN_DIR . 'vz-secure-video.php', 'vz_secure_video_update_view_db_version');


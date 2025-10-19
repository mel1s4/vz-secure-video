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

/**
 * Create permissions database table
 */
function vz_secure_video_create_permissions_table() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		// Permissions table
		$table_permissions = $wpdb->prefix . 'vz_video_permissions';
		$sql_permissions = "CREATE TABLE IF NOT EXISTS $table_permissions (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id bigint(20) UNSIGNED NOT NULL,
				user_id bigint(20) UNSIGNED NOT NULL,
				view_limit int(11) DEFAULT NULL,
				views_used int(11) DEFAULT 0,
				granted_by bigint(20) UNSIGNED DEFAULT NULL,
				granted_at datetime DEFAULT CURRENT_TIMESTAMP,
				expires_at datetime DEFAULT NULL,
				status varchar(20) DEFAULT 'active',
				PRIMARY KEY (id),
				UNIQUE KEY unique_permission (post_id, user_id),
				KEY post_id (post_id),
				KEY user_id (user_id),
				KEY status (status)
		) $charset_collate;";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_permissions);
		
		// Add permission_id column to existing view_log table if it doesn't exist
		$table_log = $wpdb->prefix . 'vz_video_view_log';
		$column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_log LIKE 'permission_id'");
		
		if (empty($column_exists)) {
				$wpdb->query("ALTER TABLE $table_log ADD COLUMN permission_id bigint(20) UNSIGNED DEFAULT NULL AFTER id");
				$wpdb->query("ALTER TABLE $table_log ADD KEY permission_id (permission_id)");
		}
}

/**
 * Update database version for permissions
 */
function vz_secure_video_update_permissions_db_version() {
		update_option('vz_secure_video_permissions_db_version', '1.0.0');
}

// Hook into activation
register_activation_hook(VZ_SECURE_VIDEO_PLUGIN_DIR . 'vz-secure-video.php', 'vz_secure_video_create_view_tables');
register_activation_hook(VZ_SECURE_VIDEO_PLUGIN_DIR . 'vz-secure-video.php', 'vz_secure_video_update_view_db_version');
register_activation_hook(VZ_SECURE_VIDEO_PLUGIN_DIR . 'vz-secure-video.php', 'vz_secure_video_create_permissions_table');
register_activation_hook(VZ_SECURE_VIDEO_PLUGIN_DIR . 'vz-secure-video.php', 'vz_secure_video_update_permissions_db_version');


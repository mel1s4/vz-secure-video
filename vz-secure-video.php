<?php
/**
 * Plugin Name: Viroz Secure Video
 * Plugin URI: https://viroz.com
 * Description: A WordPress plugin that enables secure video streaming with granular access control and time-based permissions.
 * Version: 1.0.0
 * Author: Melisa Viroz
 * Author URI: https://melisaviroz.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vz-secure-video
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
		exit;
}

// Define plugin constants
define('VZ_SECURE_VIDEO_VERSION', '1.0.0');
define('VZ_SECURE_VIDEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VZ_SECURE_VIDEO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VZ_SECURE_VIDEO_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'vz_secure_video_activate');

function vz_secure_video_activate() {
		// Flush rewrite rules to register custom post types
		flush_rewrite_rules();
}

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, 'vz_secure_video_deactivate');

function vz_secure_video_deactivate() {
		// Flush rewrite rules on deactivation
		flush_rewrite_rules();
}

/**
 * Include core functionality files
 */
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-post-type.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-meta-boxes.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-file-handler.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-template-loader.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-helpers.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-database.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-view-tracker.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-permissions.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-user-data-export.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-sv-user-data-deletion.php';

/**
 * Include admin functionality
 */
if (is_admin()) {
	require_once VZ_SECURE_VIDEO_PLUGIN_DIR
			. 'admin/vz-secure-video-admin.php';
	require_once VZ_SECURE_VIDEO_PLUGIN_DIR
			. 'includes/admin/vz-sv-privacy-settings.php';
}

/**
 * Initialize the plugin
 */
add_action('plugins_loaded', 'vz_secure_video_init');

function vz_secure_video_init() {
		// Load text domain for translations
		load_plugin_textdomain(
				'vz-secure-video',
				false,
				dirname(VZ_SECURE_VIDEO_PLUGIN_BASENAME) . '/languages'
		);
}

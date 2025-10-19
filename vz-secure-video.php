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
		. 'includes/vz-secure-video-post-type.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-secure-video-meta-boxes.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-secure-video-file-handler.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-secure-video-template-loader.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-secure-video-helpers.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-secure-video-database.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-secure-video-view-tracker.php';
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
		. 'includes/vz-secure-video-permissions.php';

/**
 * Include admin functionality
 */
if (is_admin()) {
		require_once VZ_SECURE_VIDEO_PLUGIN_DIR
				. 'admin/vz-secure-video-admin.php';
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

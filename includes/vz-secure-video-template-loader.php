<?php
/**
 * Template Loader Functions
 * 
 * Handles template loading for custom post types
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load custom template for secure video single posts
 */
function vz_secure_video_load_template() {
    if (is_singular('vz_secure_video')) {
        // Load our custom template
        include VZ_SECURE_VIDEO_PLUGIN_DIR
            . 'templates/single-vz_secure_video.php';
        exit;
    }
}

// Hook into WordPress
add_action('template_redirect', 'vz_secure_video_load_template');


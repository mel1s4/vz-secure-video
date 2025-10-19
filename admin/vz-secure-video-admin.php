<?php
/**
 * Admin Functions
 * 
 * Handles admin-specific functionality
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue admin scripts for media selector
 * 
 * @param string $hook The current admin page hook
 */
function vz_secure_video_enqueue_admin_scripts($hook) {
    global $post_type;
    
    // Only load on our custom post type edit pages
    if ($post_type !== 'vz_secure_video'
        || ($hook !== 'post.php' && $hook !== 'post-new.php')
    ) {
        return;
    }
    
    // Enqueue WordPress media uploader
    wp_enqueue_media();
    
    // Enqueue custom script
    wp_enqueue_script(
        'vz-secure-video-admin',
        VZ_SECURE_VIDEO_PLUGIN_URL . 'admin/js/media-selector.js',
        array('jquery'),
        VZ_SECURE_VIDEO_VERSION,
        true
    );
    
    // Localize script with translations
    wp_localize_script(
        'vz-secure-video-admin',
        'vzSecureVideo',
        array(
            'videoTitle' => __('Select Video File', 'vz-secure-video'),
            'zipTitle' => __('Select ZIP File', 'vz-secure-video'),
            'button' => __('Use this file', 'vz-secure-video'),
            'selectedLabel' => __(
                'Selected file:',
                'vz-secure-video'
            ),
            'removeLabel' => __('Remove File', 'vz-secure-video'),
        )
    );
}

/**
 * Show admin notice about large file uploads
 */
function vz_secure_video_large_file_notice() {
    global $post_type, $pagenow;
    
    // Only show on our custom post type edit pages
    if ($post_type !== 'vz_secure_video'
        || ($pagenow !== 'post.php' && $pagenow !== 'post-new.php')
    ) {
        return;
    }
    
    // Check if user has dismissed this notice
    $dismissed = get_user_meta(
        get_current_user_id(),
        'vz_secure_video_dismiss_large_file_notice',
        true
    );
    if ($dismissed) {
        return;
    }
    
    // Check if Big File Uploads plugin is active
    if (class_exists('BigFileUploads')) {
        return;
    }
    
    // Prepare template variables
    $install_plugins_url = admin_url(
        'themes.php?page=vz-secure-video-install-plugins'
    );
    $nonce = wp_create_nonce('vz_secure_video_dismiss_notice');
    
    // Load template
    include VZ_SECURE_VIDEO_PLUGIN_DIR
        . 'templates/admin-notice-large-files.php';
}

/**
 * Handle notice dismissal via AJAX
 */
function vz_secure_video_dismiss_large_file_notice() {
    check_ajax_referer('vz_secure_video_dismiss_notice', 'nonce');
    update_user_meta(
        get_current_user_id(),
        'vz_secure_video_dismiss_large_file_notice',
        true
    );
    wp_send_json_success();
}

// Hook into WordPress
add_action('admin_enqueue_scripts', 'vz_secure_video_enqueue_admin_scripts');
add_action('admin_notices', 'vz_secure_video_large_file_notice');
add_action(
    'wp_ajax_vz_secure_video_dismiss_large_file_notice',
    'vz_secure_video_dismiss_large_file_notice'
);


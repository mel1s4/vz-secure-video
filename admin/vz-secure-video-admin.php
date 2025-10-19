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
		global $post_type, $post;
		
		// Only load on our custom post type edit pages
		if ($post_type !== 'vz_secure_video'
				|| ($hook !== 'post.php' && $hook !== 'post-new.php')
		) {
				return;
		}
		
		// Enqueue WordPress media uploader
		wp_enqueue_media();
		
		// Enqueue media selector script
		wp_enqueue_script(
				'vz-secure-video-admin',
				VZ_SECURE_VIDEO_PLUGIN_URL . 'admin/js/media-selector.js',
				array('jquery'),
				VZ_SECURE_VIDEO_VERSION,
				true
		);
		
		// Localize media selector script with translations
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
		
		// Enqueue permissions manager script
		wp_enqueue_script(
				'vz-secure-video-permissions',
				VZ_SECURE_VIDEO_PLUGIN_URL . 'admin/js/permissions-manager.js',
				array('jquery'),
				VZ_SECURE_VIDEO_VERSION,
				true
		);
		
		// Localize permissions manager script with data
		wp_localize_script(
				'vz-secure-video-permissions',
				'vzPermissionsData',
				array(
						'postId' => $post ? $post->ID : 0,
						'grantNonce' => wp_create_nonce('vz_grant_permission'),
						'revokeNonce' => wp_create_nonce('vz_revoke_permission'),
						'messages' => array(
								'selectUser' => __('Please select a user.', 'vz-secure-video'),
								'grantFailed' => __('Failed to grant permission.', 'vz-secure-video'),
								'revokeFailed' => __('Failed to revoke permission.', 'vz-secure-video'),
								'revokeConfirm' => __('Are you sure you want to revoke this permission?', 'vz-secure-video'),
								'ajaxError' => __('An error occurred. Please try again.', 'vz-secure-video'),
						),
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

/**
 * AJAX handler to grant permission
 */
function vz_ajax_grant_permission() {
		check_ajax_referer('vz_grant_permission', 'nonce');
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
		$view_limit = isset($_POST['view_limit']) && $_POST['view_limit'] !== '' 
				? intval($_POST['view_limit']) 
				: null;
		$expires_at = isset($_POST['expires_at']) && $_POST['expires_at'] !== '' 
				? sanitize_text_field($_POST['expires_at']) 
				: null;
		
		if (!$post_id || !$user_id) {
				wp_send_json_error(array('message' => __('Invalid parameters.', 'vz-secure-video')));
				return;
		}
		
		if (!current_user_can('edit_post', $post_id)) {
				wp_send_json_error(array('message' => __('Permission denied.', 'vz-secure-video')));
				return;
		}
		
		$permission_id = vz_grant_video_permission($post_id, $user_id, $view_limit, null, $expires_at);
		
		if ($permission_id) {
				wp_send_json_success(array('message' => __('Permission granted successfully.', 'vz-secure-video')));
		} else {
				wp_send_json_error(array('message' => __('Failed to grant permission.', 'vz-secure-video')));
		}
}

/**
 * AJAX handler to revoke permission
 */
function vz_ajax_revoke_permission() {
		check_ajax_referer('vz_revoke_permission', 'nonce');
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
		
		if (!$post_id || !$user_id) {
				wp_send_json_error(array('message' => __('Invalid parameters.', 'vz-secure-video')));
				return;
		}
		
		if (!current_user_can('edit_post', $post_id)) {
				wp_send_json_error(array('message' => __('Permission denied.', 'vz-secure-video')));
				return;
		}
		
		$success = vz_revoke_video_permission($post_id, $user_id);
		
		if ($success) {
				wp_send_json_success(array('message' => __('Permission revoked successfully.', 'vz-secure-video')));
		} else {
				wp_send_json_error(array('message' => __('Failed to revoke permission.', 'vz-secure-video')));
		}
}

// Hook into WordPress
add_action('admin_enqueue_scripts', 'vz_secure_video_enqueue_admin_scripts');
add_action('admin_notices', 'vz_secure_video_large_file_notice');
add_action(
		'wp_ajax_vz_secure_video_dismiss_large_file_notice',
		'vz_secure_video_dismiss_large_file_notice'
);
add_action('wp_ajax_vz_grant_permission', 'vz_ajax_grant_permission');
add_action('wp_ajax_vz_revoke_permission', 'vz_ajax_revoke_permission');


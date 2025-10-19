<?php
/**
 * Meta Boxes Functions
 * 
 * Handles meta box registration and saving
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
		exit;
}

/**
 * Add meta box for video resources
 */
function vz_secure_video_add_meta_boxes() {
		add_meta_box(
				'vz_secure_video_resources',
				__('Video Resources', 'vz-secure-video'),
				'vz_secure_video_resources_callback',
				'vz_secure_video',
				'normal',
				'high'
		);
}

/**
 * Meta box callback function
 * 
 * @param WP_Post $post The post object
 */
function vz_secure_video_resources_callback($post) {
		// Add nonce for security
		wp_nonce_field(
				'vz_secure_video_save_resources',
				'vz_secure_video_resources_nonce'
		);
		
		// Get the saved value
		$zip_file_id = get_post_meta(
				$post->ID,
				'_vz_secure_video_zip_file',
				true
		);
		$zip_file_url = '';
		
		if ($zip_file_id) {
				$zip_file_url = wp_get_attachment_url($zip_file_id);
		}
		
		// Check extraction status
		$extracted_path = get_post_meta(
				$post->ID,
				'_vz_secure_video_extracted_path',
				true
		);
		$m3u8_file = get_post_meta(
				$post->ID,
				'_vz_secure_video_m3u8_file',
				true
		);
		
		// Load template
		include VZ_SECURE_VIDEO_PLUGIN_DIR
				. 'templates/meta-box-resources.php';
		
		// Show extraction status
		if ($extracted_path && file_exists($extracted_path)) {
				echo '<div class="notice notice-success inline" '
						. 'style="margin: 10px 0;">';
				echo '<p><strong>'
						. __('Video extracted successfully!', 'vz-secure-video')
						. '</strong></p>';
				echo '<p>'
						. __('Location:', 'vz-secure-video')
						. ' <code>'
						. esc_html($extracted_path)
						. '</code></p>';
				if ($m3u8_file) {
						echo '<p>'
								. __('M3U8 file found:', 'vz-secure-video')
								. ' <code>'
								. esc_html(basename($m3u8_file))
								. '</code></p>';
				}
				echo '</div>';
		}
}

/**
 * Save meta box data
 * 
 * @param int $post_id The post ID
 */
function vz_secure_video_save_resources($post_id) {
		// Check if nonce is set
		if (!isset($_POST['vz_secure_video_resources_nonce'])) {
				return;
		}
		
		// Verify nonce
		if (!wp_verify_nonce(
				$_POST['vz_secure_video_resources_nonce'],
				'vz_secure_video_save_resources'
		)) {
				return;
		}
		
		// Check if user has permissions to save data
		if (!current_user_can('edit_post', $post_id)) {
				return;
		}
		
		// Check if not an autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
		}
		
		// Handle video file (MP4, WebM, OGG, etc.)
		$old_video_file_id = get_post_meta(
				$post_id,
				'_vz_secure_video_video_file',
				true
		);
		
		if (isset($_POST['vz_secure_video_video_file'])) {
				$new_video_file_id = sanitize_text_field(
						$_POST['vz_secure_video_video_file']
				);
				update_post_meta(
						$post_id,
						'_vz_secure_video_video_file',
						$new_video_file_id
				);
				
				// If video file changed, handle it appropriately
				if ($new_video_file_id
						&& $new_video_file_id !== $old_video_file_id
				) {
						$mime_type = get_post_mime_type($new_video_file_id);
						
						// If it's a ZIP file, extract it
						if ($mime_type === 'application/zip') {
								vz_secure_video_extract_zip(
										$post_id,
										$new_video_file_id
								);
						}
				}
		} else {
				// Video file was removed
				if ($old_video_file_id) {
						delete_post_meta(
								$post_id,
								'_vz_secure_video_video_file'
						);
				}
		}
		
		// Handle legacy ZIP file
		$old_zip_file_id = get_post_meta(
				$post_id,
				'_vz_secure_video_zip_file',
				true
		);
		
		if (isset($_POST['vz_secure_video_zip_file'])) {
				$new_zip_file_id = sanitize_text_field(
						$_POST['vz_secure_video_zip_file']
				);
				update_post_meta(
						$post_id,
						'_vz_secure_video_zip_file',
						$new_zip_file_id
				);
				
				// If ZIP file changed, extract it
				if ($new_zip_file_id && $new_zip_file_id !== $old_zip_file_id) {
						vz_secure_video_extract_zip(
								$post_id,
								$new_zip_file_id
						);
				}
		} else {
				// ZIP file was removed, clean up extracted files
				if ($old_zip_file_id) {
						vz_secure_video_cleanup_extracted_files($post_id);
				}
		}
}

/**
 * Add meta box for video permissions
 */
function vz_secure_video_add_permissions_meta_box() {
		add_meta_box(
				'vz_secure_video_permissions',
				__('Video Permissions', 'vz-secure-video'),
				'vz_secure_video_permissions_callback',
				'vz_secure_video',
				'normal',
				'high'
		);
}

/**
 * Permissions meta box callback
 */
function vz_secure_video_permissions_callback($post) {
		$post_id = $post->ID;
		include VZ_SECURE_VIDEO_PLUGIN_DIR . 'templates/meta-box-permissions.php';
}

// Hook into WordPress
add_action('add_meta_boxes', 'vz_secure_video_add_meta_boxes');
add_action('add_meta_boxes', 'vz_secure_video_add_permissions_meta_box');
add_action('save_post_vz_secure_video', 'vz_secure_video_save_resources');


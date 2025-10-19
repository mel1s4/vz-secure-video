<?php
/**
 * View Tracking Functions
 * 
 * Handles view count tracking for secure videos
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
		exit;
}

/**
 * AJAX handler to track video views
 */
function vz_track_video_view() {
		// Verify nonce
		if (!isset($_POST['nonce']) 
				|| !wp_verify_nonce($_POST['nonce'], 'vz_track_view')
		) {
				wp_send_json_error(
					['message' => 'Invalid security token']
				);
				return;
		}
		
		// Get post ID
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		
		// Validate post ID and post type
		if (!$post_id || get_post_type($post_id) !== 'vz_secure_video') {
				wp_send_json_error(['message' => 'Invalid video']);
				return;
		}
		
		// Get current user ID
		$user_id = get_current_user_id();
		
		// Check if user has permission to view this video
		if (!vz_user_can_view_video($post_id, $user_id)) {
				wp_send_json_error(
					['message' => 'You do not have permission to view this video.']
				);
				return;
		}
		
		// Check if video is public
		$is_public = get_post_meta($post_id, '_vz_video_public_access', true);
		
		if ($is_public === '1') {
				// For public videos, record view without permission check
				$success = vz_record_public_video_view($post_id, $user_id);
		} else {
				// For secure videos, record the view using permissions system
				$success = vz_record_video_view($post_id, $user_id);
		}
		
		if (!$success) {
				wp_send_json_error(['message' => 'Failed to record view']);
				return;
		}
		
		// Update cache (increment counts)
		vz_update_view_cache($post_id, $user_id);
		
		// Get updated counts and remaining views
		$counts = vz_get_video_view_counts($post_id);
		$remaining = $is_public === '1' ? null : vz_get_remaining_views($post_id, $user_id);
		
		// Return success with counts and remaining views
		wp_send_json_success([
				'total_views' => $counts['total'],
				'unique_views' => $counts['unique'],
				'remaining_views' => $remaining,
				'message' => 'View tracked successfully'
		]);
}

// Register AJAX handler for both logged-in and non-logged-in users
// (for public videos)
add_action('wp_ajax_vz_track_video_view', 'vz_track_video_view');
add_action('wp_ajax_nopriv_vz_track_video_view', 'vz_track_video_view');

/**
 * Get view counts for a video (from cache or calculate)
 * 
 * @param int $post_id The video post ID
 * @return array Array with 'total' and 'unique' counts
 */
function vz_get_video_view_counts($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		global $wpdb;
		$table_cache = $wpdb->prefix . 'vz_video_view_cache';
		
		// Try to get from cache
		$cache = $wpdb->get_row($wpdb->prepare(
				"SELECT total_views, unique_views FROM $table_cache WHERE post_id = %d",
				$post_id
		));
		
		if ($cache) {
				return array(
						'total' => intval($cache->total_views),
						'unique' => intval($cache->unique_views)
				);
		}
		
		// Cache miss - calculate and store
		return vz_calculate_and_cache_views($post_id);
}

/**
 * Calculate view counts and update cache
 * 
 * @param int $post_id The video post ID
 * @return array Array with 'total' and 'unique' counts
 */
function vz_calculate_and_cache_views($post_id) {
		global $wpdb;
		$table_log = $wpdb->prefix . 'vz_video_view_log';
		$table_cache = $wpdb->prefix . 'vz_video_view_cache';
		
		// Calculate total views
		$total = $wpdb->get_var($wpdb->prepare(
				"SELECT COUNT(*) FROM $table_log WHERE post_id = %d",
				$post_id
		));
		
		// Calculate unique views
		$unique = $wpdb->get_var($wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id) FROM $table_log WHERE post_id = %d",
				$post_id
		));
		
		// Update or insert cache
		$wpdb->replace(
				$table_cache,
				array(
						'post_id' => $post_id,
						'total_views' => $total,
						'unique_views' => $unique,
						'last_calculated' => current_time('mysql')
				),
				array('%d', '%d', '%d', '%s')
		);
		
		return array(
				'total' => intval($total),
				'unique' => intval($unique)
		);
}

/**
 * Update view cache for a video (increment counts)
 * 
 * @param int $post_id The video post ID
 * @param int|null $user_id The user ID (optional)
 */
function vz_update_view_cache($post_id, $user_id = null) {
		global $wpdb;
		$table_cache = $wpdb->prefix . 'vz_video_view_cache';
		
		if (!$user_id) {
				$user_id = get_current_user_id();
		}
		
		// Increment total views
		$wpdb->query($wpdb->prepare(
				"INSERT INTO $table_cache (post_id, total_views, unique_views) 
				 VALUES (%d, 1, 0) 
				 ON DUPLICATE KEY UPDATE total_views = total_views + 1",
				$post_id
		));
		
		// Update unique views if needed
		$table_log = $wpdb->prefix . 'vz_video_view_log';
		
		// Check if this is user's first view
		$first_view = $wpdb->get_var($wpdb->prepare(
				"SELECT COUNT(*) FROM $table_log WHERE post_id = %d AND user_id = %d",
				$post_id,
				$user_id
		)) == 1;
		
		if ($first_view && $user_id > 0) {
				$wpdb->query($wpdb->prepare(
						"UPDATE $table_cache SET unique_views = " .
						"unique_views + 1 WHERE post_id = %d",
						$post_id
				));
		}
}

/**
 * Get total view count for a video
 * 
 * @param int $post_id The video post ID
 * @return int The view count
 */
function vz_get_video_view_count($post_id = null) {
		$counts = vz_get_video_view_counts($post_id);
		return $counts['total'];
}

/**
 * Get unique view count for a video
 * 
 * @param int $post_id The video post ID
 * @return int The unique view count
 */
function vz_get_video_unique_view_count($post_id = null) {
		$counts = vz_get_video_view_counts($post_id);
		return $counts['unique'];
}

/**
 * Reset view count for a video
 * 
 * @param int $post_id The video post ID
 * @return bool Success status
 */
function vz_reset_video_view_count($post_id) {
		global $wpdb;
		$table_log = $wpdb->prefix . 'vz_video_view_log';
		$table_cache = $wpdb->prefix . 'vz_video_view_cache';
		
		// Delete all view logs
		$wpdb->delete($table_log, array('post_id' => $post_id), array('%d'));
		
		// Reset cache
		$wpdb->delete($table_cache, array('post_id' => $post_id), array('%d'));
		
		return true;
}

/**
 * Get view history for a video
 * 
 * @param int $post_id The video post ID
 * @param int $limit Number of views to return
 * @return array Array of view objects
 */
function vz_get_video_view_history($post_id, $limit = 50) {
		global $wpdb;
		$table_log = $wpdb->prefix . 'vz_video_view_log';
		
		return $wpdb->get_results($wpdb->prepare(
				"SELECT l.*, u.display_name, u.user_email 
				 FROM $table_log l 
				 LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID 
				 WHERE l.post_id = %d 
				 ORDER BY l.viewed_at DESC 
				 LIMIT %d",
				$post_id,
				$limit
		));
}


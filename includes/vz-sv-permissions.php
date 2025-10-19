<?php
/**
 * Permission Functions
 * 
 * Handles video access permissions and view tracking
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
		exit;
}

/**
 * Grant permission to a user for a video
 * 
 * @param int $post_id Video post ID
 * @param int $user_id User ID
 * @param int|null $view_limit Number of views allowed (NULL for unlimited)
 * @param int $granted_by User ID who granted permission
 * @param string|null $expires_at Expiration date/time 
 *                                  (NULL for no expiration)
 * @return int|false Permission ID or false on failure
 */
function vz_grant_video_permission(
	$post_id, 
	$user_id, 
	$view_limit = null, 
	$granted_by = null, 
	$expires_at = null
) {
		global $wpdb;
		
		// Validate inputs
		if (!$post_id || !$user_id) {
				return false;
		}
		
		// Check if video exists
		if (get_post_type($post_id) !== 'vz_secure_video') {
				return false;
		}
		
		// Check if user exists
		if (!get_userdata($user_id)) {
				return false;
		}
		
		$table = $wpdb->prefix . 'vz_video_permissions';
		
		// Check if permission already exists
		$existing = $wpdb->get_var($wpdb->prepare(
				"SELECT id FROM $table WHERE post_id = %d AND user_id = %d",
				$post_id,
				$user_id
		));
		
		if ($existing) {
				// Update existing permission
				$wpdb->update(
						$table,
						array(
								'view_limit' => $view_limit,
								'views_used' => 0, // Reset views
								'granted_by' => $granted_by ?: get_current_user_id(),
								'expires_at' => $expires_at,
								'status' => 'active'
						),
						array('id' => $existing),
						array('%d', '%d', '%d', '%s', '%s'),
						array('%d')
				);
				return $existing;
		} else {
				// Insert new permission
				$wpdb->insert(
						$table,
						array(
								'post_id' => $post_id,
								'user_id' => $user_id,
								'view_limit' => $view_limit,
								'views_used' => 0,
								'granted_by' => $granted_by ?: get_current_user_id(),
								'expires_at' => $expires_at,
								'status' => 'active'
						),
						array('%d', '%d', '%d', '%d', '%d', '%s', '%s')
				);
				return $wpdb->insert_id;
		}
}

/**
 * Revoke permission for a user
 * 
 * @param int $post_id Video post ID
 * @param int $user_id User ID
 * @return bool Success status
 */
function vz_revoke_video_permission($post_id, $user_id) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'vz_video_permissions';
		
		return $wpdb->delete(
				$table,
				array('post_id' => $post_id, 'user_id' => $user_id),
				array('%d', '%d')
		) !== false;
}

/**
 * Check if user has permission to view video
 * 
 * @param int $post_id Video post ID
 * @param int|null $user_id User ID (defaults to current user)
 * @return bool True if user can view
 */
function vz_user_can_view_video($post_id, $user_id = null) {
		// Admins always have access
		if (current_user_can('manage_options')) {
				return true;
		}
		
		// Get current user if not specified
		if (!$user_id) {
				$user_id = get_current_user_id();
		}
		
		// Guests cannot view
		if (!$user_id) {
				return false;
		}
		
		global $wpdb;
		$table = $wpdb->prefix . 'vz_video_permissions';
		
		$permission = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $table WHERE post_id = %d " .
				"AND user_id = %d AND status = 'active'",
				$post_id,
				$user_id
		));
		
		if (!$permission) {
				return false;
		}
		
		// Check if permission has expired
		if ($permission->expires_at) {
				$expires_timestamp = strtotime($permission->expires_at);
				if (current_time('timestamp') > $expires_timestamp) {
						// Mark as expired
						$wpdb->update(
								$table,
								array('status' => 'expired'),
								array('id' => $permission->id),
								array('%s'),
								array('%d')
						);
						return false;
				}
		}
		
		// Check if views are unlimited
		if ($permission->view_limit === null) {
				return true;
		}
		
		// Check if views remaining
		return $permission->views_used < $permission->view_limit;
}

/**
 * Get remaining views for a user
 * 
 * @param int $post_id Video post ID
 * @param int|null $user_id User ID
 * @return int|null Remaining views (NULL for unlimited)
 */
function vz_get_remaining_views($post_id, $user_id = null) {
		if (!$user_id) {
				$user_id = get_current_user_id();
		}
		
		global $wpdb;
		$table = $wpdb->prefix . 'vz_video_permissions';
		
		$permission = $wpdb->get_row($wpdb->prepare(
				"SELECT view_limit, views_used FROM $table " .
				"WHERE post_id = %d AND user_id = %d AND status = 'active'",
				$post_id,
				$user_id
		));
		
		if (!$permission) {
				return null;
		}
		
		if ($permission->view_limit === null) {
				return null; // Unlimited
		}
		
		return max(0, $permission->view_limit - $permission->views_used);
}

/**
 * Record a video view
 * 
 * @param int $post_id Video post ID
 * @param int|null $user_id User ID
 * @return bool Success status
 */
function vz_record_video_view($post_id, $user_id = null) {
		if (!$user_id) {
				$user_id = get_current_user_id();
		}
		
		// Check if user has permission
		if (!vz_user_can_view_video($post_id, $user_id)) {
				return false;
		}
		
		global $wpdb;
		$table_permissions = $wpdb->prefix . 'vz_video_permissions';
		$table_log = $wpdb->prefix . 'vz_video_view_log';
		
		// Get permission
		$permission = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $table_permissions WHERE post_id = %d " .
				"AND user_id = %d AND status = 'active'",
				$post_id,
				$user_id
		));
		
		if (!$permission) {
				return false;
		}
		
		// Increment views_used
		$wpdb->update(
				$table_permissions,
				array('views_used' => $permission->views_used + 1),
				array('id' => $permission->id),
				array('%d'),
				array('%d')
		);
		
		// Log the view
		$wpdb->insert(
				$table_log,
				array(
						'permission_id' => $permission->id,
						'post_id' => $post_id,
						'user_id' => $user_id,
						'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
						'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
				),
				array('%d', '%d', '%d', '%s', '%s')
		);
		
		return true;
}

/**
 * Get all permissions for a video
 * 
 * @param int $post_id Video post ID
 * @return array Array of permission objects
 */
function vz_get_video_permissions($post_id) {
		global $wpdb;
		$table = $wpdb->prefix . 'vz_video_permissions';
		
		return $wpdb->get_results($wpdb->prepare(
				"SELECT p.*, u.display_name, u.user_email 
				FROM $table p 
				LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID 
				WHERE p.post_id = %d 
				ORDER BY p.granted_at DESC",
				$post_id
		));
}

/**
 * Get all videos a user has permission to view
 * 
 * @param int $user_id User ID
 * @return array Array of video post IDs
 */
function vz_get_user_accessible_videos($user_id) {
		global $wpdb;
		$table = $wpdb->prefix . 'vz_video_permissions';
		
		$results = $wpdb->get_col($wpdb->prepare(
				"SELECT post_id FROM $table WHERE user_id = %d AND status = 'active'",
				$user_id
		));
		
		return array_map('intval', $results);
}


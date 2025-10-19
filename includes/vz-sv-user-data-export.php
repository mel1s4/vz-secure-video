<?php
/**
 * User Data Export Functions
 * 
 * Handles GDPR data export functionality
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Export user data
 * 
 * @param int $user_id User ID
 * @param string $format Export format (json or csv)
 * @return array|WP_Error Exported data or error
 */
function vz_export_user_data($user_id, $format = 'json') {
	if (!vz_is_data_export_enabled()) {
		return new WP_Error(
			'export_disabled',
			__('Data export is not enabled.', 'vz-secure-video')
		);
	}
	
	// Check if user can export their own data
	if (!current_user_can('manage_options') 
			&& get_current_user_id() !== $user_id
	) {
		return new WP_Error(
			'permission_denied',
			__(
				'You do not have permission to export this user\'s data.',
				'vz-secure-video'
			)
		);
	}
	
	$user = get_userdata($user_id);
	if (!$user) {
		return new WP_Error(
			'invalid_user',
			__('Invalid user ID.', 'vz-secure-video')
		);
	}
	
	// Collect all user data
	$data = array(
		'user_info' => array(
			'user_id' => $user->ID,
			'username' => $user->user_login,
			'email' => $user->user_email,
			'display_name' => $user->display_name,
			'registered_date' => $user->user_registered,
		),
		'video_permissions' => vz_get_user_permissions_for_export($user_id),
		'view_history' => vz_get_user_view_history_for_export($user_id),
		'analytics' => vz_get_user_analytics_for_export($user_id),
		'export_date' => current_time('mysql'),
		'export_timestamp' => current_time('timestamp'),
	);
	
	// Format the data
	if ($format === 'csv') {
		return vz_format_user_data_csv($data);
	}
	
	return $data;
}

/**
 * Get user permissions for export
 * 
 * @param int $user_id User ID
 * @return array User permissions
 */
function vz_get_user_permissions_for_export($user_id) {
	global $wpdb;
	
	$table_permissions = $wpdb->prefix . 'vz_video_permissions';
	
	$permissions = $wpdb->get_results($wpdb->prepare(
		"SELECT 
			p.id,
			p.post_id,
			p.view_limit,
			p.views_used,
			p.granted_at,
			p.expires_at,
			post.post_title as video_title
		FROM $table_permissions p
		LEFT JOIN {$wpdb->posts} post ON p.post_id = post.ID
		WHERE p.user_id = %d
		ORDER BY p.granted_at DESC",
		$user_id
	), ARRAY_A);
	
	return $permissions ? $permissions : array();
}

/**
 * Get user view history for export
 * 
 * @param int $user_id User ID
 * @return array View history
 */
function vz_get_user_view_history_for_export($user_id) {
	global $wpdb;
	
	$table_log = $wpdb->prefix . 'vz_video_view_log';
	
	// Get IP address handling based on settings
	$ip_field = vz_should_anonymize_ip() 
		? "CONCAT(SUBSTRING_INDEX(ip_address, '.', 3), '.0') as ip_address"
		: 'ip_address';
	
	$views = $wpdb->get_results($wpdb->prepare(
		"SELECT 
			l.id,
			l.post_id,
			l.viewed_at,
			l.view_duration,
			$ip_field,
			post.post_title as video_title
		FROM $table_log l
		LEFT JOIN {$wpdb->posts} post ON l.post_id = post.ID
		WHERE l.user_id = %d
		ORDER BY l.viewed_at DESC
		LIMIT 1000",
		$user_id
	), ARRAY_A);
	
	return $views ? $views : array();
}

/**
 * Get user analytics for export
 * 
 * @param int $user_id User ID
 * @return array Analytics data
 */
function vz_get_user_analytics_for_export($user_id) {
	global $wpdb;
	
	$table_log = $wpdb->prefix . 'vz_video_view_log';
	
	$analytics = array(
		'total_views' => 0,
		'total_watch_time' => 0,
		'average_watch_time' => 0,
		'most_watched_videos' => array(),
		'watch_times_by_day' => array(),
	);
	
	// Get total views and watch time
	$stats = $wpdb->get_row($wpdb->prepare(
		"SELECT 
			COUNT(*) as total_views,
			SUM(view_duration) as total_watch_time
		FROM $table_log
		WHERE user_id = %d",
		$user_id
	), ARRAY_A);
	
	if ($stats) {
		$analytics['total_views'] = (int) $stats['total_views'];
		$analytics['total_watch_time'] = (int) $stats['total_watch_time'];
		$analytics['average_watch_time'] = $stats['total_views'] > 0 
			? round($stats['total_watch_time'] / $stats['total_views'], 2)
			: 0;
	}
	
	// Get most watched videos
	$most_watched = $wpdb->get_results($wpdb->prepare(
		"SELECT 
			l.post_id,
			post.post_title as video_title,
			COUNT(*) as view_count
		FROM $table_log l
		LEFT JOIN {$wpdb->posts} post ON l.post_id = post.ID
		WHERE l.user_id = %d
		GROUP BY l.post_id
		ORDER BY view_count DESC
		LIMIT 10",
		$user_id
	), ARRAY_A);
	
	if ($most_watched) {
		$analytics['most_watched_videos'] = $most_watched;
	}
	
	// Get watch times by day of week
	$watch_by_day = $wpdb->get_results($wpdb->prepare(
		"SELECT 
			DAYNAME(viewed_at) as day_name,
			DAYOFWEEK(viewed_at) as day_number,
			COUNT(*) as view_count
		FROM $table_log
		WHERE user_id = %d
		GROUP BY DAYOFWEEK(viewed_at)
		ORDER BY day_number",
		$user_id
	), ARRAY_A);
	
	if ($watch_by_day) {
		$analytics['watch_times_by_day'] = $watch_by_day;
	}
	
	return $analytics;
}

/**
 * Format user data as CSV
 * 
 * @param array $data User data
 * @return string CSV formatted data
 */
function vz_format_user_data_csv($data) {
	$csv = array();
	
	// User info
	$csv[] = '=== USER INFORMATION ===';
	$csv[] = 'Field,Value';
	foreach ($data['user_info'] as $key => $value) {
		$csv[] = sprintf('"%s","%s"', $key, $value);
	}
	
	// Permissions
	$csv[] = '';
	$csv[] = '=== VIDEO PERMISSIONS ===';
	if (!empty($data['video_permissions'])) {
		$csv[] = 'Video Title,View Limit,Views Used,Granted At,Expires At';
		foreach ($data['video_permissions'] as $perm) {
			$csv[] = sprintf(
				'"%s","%s","%s","%s","%s"',
				$perm['video_title'],
				$perm['view_limit'] ?: 'Unlimited',
				$perm['views_used'],
				$perm['granted_at'],
				$perm['expires_at'] ?: 'Never'
			);
		}
	} else {
		$csv[] = 'No permissions found.';
	}
	
	// View history
	$csv[] = '';
	$csv[] = '=== VIEW HISTORY ===';
	if (!empty($data['view_history'])) {
		$csv[] = 'Video Title,Viewed At,Duration (seconds),IP Address';
		foreach ($data['view_history'] as $view) {
			$csv[] = sprintf(
				'"%s","%s","%s","%s"',
				$view['video_title'],
				$view['viewed_at'],
				$view['view_duration'],
				$view['ip_address']
			);
		}
	} else {
		$csv[] = 'No view history found.';
	}
	
	// Analytics
	$csv[] = '';
	$csv[] = '=== ANALYTICS ===';
	$csv[] = 'Metric,Value';
	$csv[] = sprintf(
		'"Total Views","%d"', 
		$data['analytics']['total_views']
	);
	$csv[] = sprintf(
		'"Total Watch Time (seconds)","%d"', 
		$data['analytics']['total_watch_time']
	);
	$csv[] = sprintf(
		'"Average Watch Time (seconds)","%.2f"', 
		$data['analytics']['average_watch_time']
	);
	
	if (!empty($data['analytics']['most_watched_videos'])) {
		$csv[] = '';
		$csv[] = 'Most Watched Videos';
		$csv[] = 'Video Title,View Count';
		foreach ($data['analytics']['most_watched_videos'] as $video) {
			$csv[] = sprintf('"%s","%d"', $video['video_title'], $video['view_count']);
		}
	}
	
	return implode("\n", $csv);
}

/**
 * Check if data export is enabled
 * 
 * @return bool
 */
function vz_is_data_export_enabled() {
	return (bool) get_option('vz_allow_data_export', true);
}

/**
 * AJAX handler for user data export
 */
function vz_ajax_export_user_data() {
	// Check nonce
	if (!isset($_REQUEST['nonce']) 
			|| !wp_verify_nonce($_REQUEST['nonce'], 'vz_export_data')
	) {
		wp_send_json_error(
			array('message' => __('Security check failed.', 'vz-secure-video'))
		);
		return;
	}
	
	$user_id = isset($_REQUEST['user_id']) 
			? intval($_REQUEST['user_id']) 
			: 0;
	$format = isset($_REQUEST['format']) 
			? sanitize_text_field($_REQUEST['format']) 
			: 'json';
	
	if (!$user_id) {
		$user_id = get_current_user_id();
	}
	
	if (!$user_id) {
		wp_send_json_error(
			array('message' => __('Invalid user ID.', 'vz-secure-video'))
		);
		return;
	}
	
	$data = vz_export_user_data($user_id, $format);
	
	if (is_wp_error($data)) {
		wp_send_json_error(array('message' => $data->get_error_message()));
		return;
	}
	
	// If CSV format, send as download
	if ($format === 'csv') {
		$filename = sprintf(
			'vz-user-data-export-%d-%s.csv',
			$user_id,
			date('Y-m-d-His')
		);
		
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		echo $data;
		exit;
	}
	
	// If JSON format, send as download
	$filename = sprintf(
		'vz-user-data-export-%d-%s.json',
		$user_id,
		date('Y-m-d-His')
	);
	
	header('Content-Type: application/json');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	echo json_encode($data, JSON_PRETTY_PRINT);
	exit;
}

/**
 * Add export button to user profile page
 */
function vz_add_export_button_to_profile($user) {
	if (!vz_is_data_export_enabled()) {
		return;
	}
	
	// Only show to admins or the user themselves
	if (!current_user_can('manage_options') 
			&& get_current_user_id() !== $user->ID
	) {
		return;
	}
	?>
	<h2><?php _e('Video Data Export', 'vz-secure-video'); ?></h2>
	<table class="form-table">
		<tr>
			<th><?php _e('Export Your Data', 'vz-secure-video'); ?></th>
			<td>
				<p>
					<?php _e(
						'Download a copy of all your video viewing data in ' .
						'JSON or CSV format.',
						'vz-secure-video'
					); ?>
				</p>
				<p>
					<a href="<?php echo esc_url(add_query_arg(array(
						'action' => 'vz_export_user_data',
						'user_id' => $user->ID,
						'format' => 'json',
						'nonce' => wp_create_nonce('vz_export_data')
					), admin_url('admin-ajax.php'))); ?>" class="button">
						<?php _e('Export as JSON', 'vz-secure-video'); ?>
					</a>
					<a href="<?php echo esc_url(add_query_arg(array(
						'action' => 'vz_export_user_data',
						'user_id' => $user->ID,
						'format' => 'csv',
						'nonce' => wp_create_nonce('vz_export_data')
					), admin_url('admin-ajax.php'))); ?>" class="button">
						<?php _e('Export as CSV', 'vz-secure-video'); ?>
					</a>
				</p>
				<p class="description">
					<?php _e(
						'This will include your video permissions, view ' .
						'history, and analytics data.',
						'vz-secure-video'
					); ?>
				</p>
			</td>
		</tr>
	</table>
	<?php
}

// Hook into WordPress
add_action('wp_ajax_vz_export_user_data', 'vz_ajax_export_user_data');
add_action('show_user_profile', 'vz_add_export_button_to_profile');
add_action('edit_user_profile', 'vz_add_export_button_to_profile');


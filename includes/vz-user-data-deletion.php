<?php
/**
 * User Data Deletion Functions
 * 
 * Handles GDPR data deletion functionality
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Delete user data
 * 
 * @param int $user_id User ID
 * @param bool $anonymize_only Whether to only anonymize instead of delete
 * @return bool|WP_Error Success or error
 */
function vz_delete_user_data($user_id, $anonymize_only = false) {
	if (!vz_is_data_deletion_enabled()) {
		return new WP_Error(
			'deletion_disabled',
			__('Data deletion is not enabled.', 'vz-secure-video')
		);
	}
	
	// Check if user can delete their own data
	if (!current_user_can('manage_options') && get_current_user_id() !== $user_id) {
		return new WP_Error(
			'permission_denied',
			__('You do not have permission to delete this user\'s data.', 'vz-secure-video')
		);
	}
	
	$user = get_userdata($user_id);
	if (!$user) {
		return new WP_Error(
			'invalid_user',
			__('Invalid user ID.', 'vz-secure-video')
		);
	}
	
	global $wpdb;
	
	// Start transaction
	$wpdb->query('START TRANSACTION');
	
	try {
		// Delete or anonymize video permissions
		if ($anonymize_only) {
			$result1 = vz_anonymize_video_permissions($user_id);
		} else {
			$result1 = vz_delete_video_permissions($user_id);
		}
		
		// Delete or anonymize view logs
		if ($anonymize_only) {
			$result2 = vz_anonymize_view_logs($user_id);
		} else {
			$result2 = vz_delete_view_logs($user_id);
		}
		
		// Log the deletion
		vz_log_data_deletion($user_id, $anonymize_only);
		
		// Commit transaction
		$wpdb->query('COMMIT');
		
		return true;
		
	} catch (Exception $e) {
		// Rollback transaction
		$wpdb->query('ROLLBACK');
		
		return new WP_Error(
			'deletion_failed',
			sprintf(__('Data deletion failed: %s', 'vz-secure-video'), $e->getMessage())
		);
	}
}

/**
 * Delete video permissions for a user
 * 
 * @param int $user_id User ID
 * @return bool Success
 */
function vz_delete_video_permissions($user_id) {
	global $wpdb;
	
	$table_permissions = $wpdb->prefix . 'vz_video_permissions';
	
	$result = $wpdb->delete(
		$table_permissions,
		array('user_id' => $user_id),
		array('%d')
	);
	
	return $result !== false;
}

/**
 * Anonymize video permissions for a user
 * 
 * @param int $user_id User ID
 * @return bool Success
 */
function vz_anonymize_video_permissions($user_id) {
	global $wpdb;
	
	$table_permissions = $wpdb->prefix . 'vz_video_permissions';
	
	// Set user_id to 0 to anonymize
	$result = $wpdb->update(
		$table_permissions,
		array('user_id' => 0),
		array('user_id' => $user_id),
		array('%d'),
		array('%d')
	);
	
	return $result !== false;
}

/**
 * Delete view logs for a user
 * 
 * @param int $user_id User ID
 * @return bool Success
 */
function vz_delete_view_logs($user_id) {
	global $wpdb;
	
	$table_log = $wpdb->prefix . 'vz_video_view_log';
	
	$result = $wpdb->delete(
		$table_log,
		array('user_id' => $user_id),
		array('%d')
	);
	
	return $result !== false;
}

/**
 * Anonymize view logs for a user
 * 
 * @param int $user_id User ID
 * @return bool Success
 */
function vz_anonymize_view_logs($user_id) {
	global $wpdb;
	
	$table_log = $wpdb->prefix . 'vz_video_view_log';
	
	// Anonymize user data
	$result = $wpdb->update(
		$table_log,
		array(
			'user_id' => 0,
			'ip_address' => 'anonymized',
			'user_agent' => 'anonymized'
		),
		array('user_id' => $user_id),
		array('%d', '%s', '%s'),
		array('%d')
	);
	
	return $result !== false;
}

/**
 * Log data deletion for audit purposes
 * 
 * @param int $user_id User ID
 * @param bool $anonymize_only Whether only anonymization was performed
 */
function vz_log_data_deletion($user_id, $anonymize_only = false) {
	global $wpdb;
	
	$table_log = $wpdb->prefix . 'vz_data_deletion_log';
	
	// Create table if it doesn't exist
	vz_create_deletion_log_table();
	
	$wpdb->insert(
		$table_log,
		array(
			'user_id' => $user_id,
			'deletion_type' => $anonymize_only ? 'anonymize' : 'delete',
			'deleted_by' => get_current_user_id(),
			'deleted_at' => current_time('mysql'),
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
		),
		array('%d', '%s', '%d', '%s', '%s')
	);
}

/**
 * Create deletion log table if it doesn't exist
 */
function vz_create_deletion_log_table() {
	global $wpdb;
	
	$table_log = $wpdb->prefix . 'vz_data_deletion_log';
	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE IF NOT EXISTS $table_log (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id bigint(20) UNSIGNED NOT NULL,
		deletion_type varchar(20) NOT NULL DEFAULT 'delete',
		deleted_by bigint(20) UNSIGNED NOT NULL,
		deleted_at datetime NOT NULL,
		ip_address varchar(45) NOT NULL,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY deleted_at (deleted_at)
	) $charset_collate;";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

/**
 * Check if data deletion is enabled
 * 
 * @return bool
 */
function vz_is_data_deletion_enabled() {
	return (bool) get_option('vz_allow_data_deletion', true);
}

/**
 * AJAX handler for user data deletion
 */
function vz_ajax_delete_user_data() {
	// Check nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vz_delete_data')) {
		wp_send_json_error(array('message' => __('Security check failed.', 'vz-secure-video')));
		return;
	}
	
	$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
	$anonymize_only = isset($_POST['anonymize_only']) && $_POST['anonymize_only'] === '1';
	
	if (!$user_id) {
		$user_id = get_current_user_id();
	}
	
	if (!$user_id) {
		wp_send_json_error(array('message' => __('Invalid user ID.', 'vz-secure-video')));
		return;
	}
	
	$result = vz_delete_user_data($user_id, $anonymize_only);
	
	if (is_wp_error($result)) {
		wp_send_json_error(array('message' => $result->get_error_message()));
		return;
	}
	
	$message = $anonymize_only 
		? __('Your data has been anonymized successfully.', 'vz-secure-video')
		: __('Your data has been deleted successfully.', 'vz-secure-video');
	
	wp_send_json_success(array('message' => $message));
}

/**
 * Add deletion button to user profile page
 */
function vz_add_deletion_button_to_profile($user) {
	if (!vz_is_data_deletion_enabled()) {
		return;
	}
	
	// Only show to admins or the user themselves
	if (!current_user_can('manage_options') && get_current_user_id() !== $user->ID) {
		return;
	}
	
	// Don't show if user is an admin (safety check)
	if (user_can($user->ID, 'manage_options')) {
		return;
	}
	?>
	<h2><?php _e('Data Deletion', 'vz-secure-video'); ?></h2>
	<table class="form-table">
		<tr>
			<th><?php _e('Delete Your Data', 'vz-secure-video'); ?></th>
			<td>
				<p><?php _e('Request deletion of all your video viewing data. This action cannot be undone.', 'vz-secure-video'); ?></p>
				
				<p>
					<label>
						<input type="checkbox" id="vz-anonymize-only" value="1">
						<?php _e('Anonymize instead of delete (keeps data for analytics but removes personal identifiers)', 'vz-secure-video'); ?>
					</label>
				</p>
				
				<p>
					<button type="button" class="button button-secondary" id="vz-delete-data-btn">
						<?php _e('Delete My Data', 'vz-secure-video'); ?>
					</button>
				</p>
				
				<p class="description">
					<?php _e('This will permanently delete or anonymize your video permissions, view history, and analytics data.', 'vz-secure-video'); ?>
				</p>
				
				<?php
				$privacy_policy_url = get_option('vz_privacy_policy_url');
				if ($privacy_policy_url) {
					printf(
						'<p class="description"><a href="%s" target="_blank">%s</a></p>',
						esc_url($privacy_policy_url),
						__('View our Privacy Policy', 'vz-secure-video')
					);
				}
				?>
			</td>
		</tr>
	</table>
	
	<script>
	jQuery(document).ready(function($) {
		$('#vz-delete-data-btn').on('click', function() {
			var anonymizeOnly = $('#vz-anonymize-only').is(':checked');
			var confirmMessage = anonymizeOnly
				? '<?php echo esc_js(__('Are you sure you want to anonymize all your video data? This will remove personal identifiers but keep anonymized data for analytics.', 'vz-secure-video')); ?>'
				: '<?php echo esc_js(__('Are you sure you want to permanently delete all your video data? This action cannot be undone.', 'vz-secure-video')); ?>';
			
			if (!confirm(confirmMessage)) {
				return;
			}
			
			// Show loading state
			$(this).prop('disabled', true).text('<?php echo esc_js(__('Processing...', 'vz-secure-video')); ?>');
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'vz_delete_user_data',
					user_id: <?php echo $user->ID; ?>,
					anonymize_only: anonymizeOnly ? 1 : 0,
					nonce: '<?php echo wp_create_nonce('vz_delete_data'); ?>'
				},
				success: function(response) {
					if (response.success) {
						alert(response.data.message);
						location.reload();
					} else {
						alert('<?php echo esc_js(__('Failed to delete data:', 'vz-secure-video')); ?> ' + response.data.message);
						$('#vz-delete-data-btn').prop('disabled', false).text('<?php echo esc_js(__('Delete My Data', 'vz-secure-video')); ?>');
					}
				},
				error: function() {
					alert('<?php echo esc_js(__('An error occurred. Please try again.', 'vz-secure-video')); ?>');
					$('#vz-delete-data-btn').prop('disabled', false).text('<?php echo esc_js(__('Delete My Data', 'vz-secure-video')); ?>');
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Get deletion log for a user
 * 
 * @param int $user_id User ID
 * @return array Deletion log entries
 */
function vz_get_user_deletion_log($user_id) {
	global $wpdb;
	
	$table_log = $wpdb->prefix . 'vz_data_deletion_log';
	
	$logs = $wpdb->get_results($wpdb->prepare(
		"SELECT * FROM $table_log WHERE user_id = %d ORDER BY deleted_at DESC",
		$user_id
	), ARRAY_A);
	
	return $logs ? $logs : array();
}

// Hook into WordPress
add_action('wp_ajax_vz_delete_user_data', 'vz_ajax_delete_user_data');
add_action('show_user_profile', 'vz_add_deletion_button_to_profile');
add_action('edit_user_profile', 'vz_add_deletion_button_to_profile');


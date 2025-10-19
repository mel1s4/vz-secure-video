<?php
/**
 * Template for Video Permissions Meta Box
 * 
 * @var int $post_id Post ID
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Get existing permissions
$permissions = vz_get_video_permissions($post_id);

// Get public access setting
$is_public = get_post_meta($post_id, '_vz_video_public_access', true);
?>

<div class="vz-permissions-container">
	<!-- Public Access Setting -->
	<div class="vz-public-access" style="margin-bottom: 30px; padding: 15px; background: #f0f0f1; border-left: 4px solid #2271b1;">
		<h3 style="margin-top: 0;"><?php _e('Access Control', 'vz-secure-video'); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="vz_video_public_access">
						<?php _e('Public Access', 'vz-secure-video'); ?>
					</label>
				</th>
				<td>
					<label>
						<input type="checkbox" 
									 name="vz_video_public_access" 
									 id="vz_video_public_access" 
									 value="1" 
									 <?php checked($is_public, '1'); ?> />
						<?php _e('Make this video publicly accessible with no restrictions', 'vz-secure-video'); ?>
					</label>
					<p class="description">
						<?php _e('When enabled, anyone can view this video without logging in or requiring specific permissions. Individual user permissions below will be ignored.', 'vz-secure-video'); ?>
					</p>
				</td>
			</tr>
		</table>
	</div>

	<!-- Add User Permission -->
	<div class="vz-add-permission" style="margin-bottom: 20px;">
		<h3><?php _e('Grant Access', 'vz-secure-video'); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="vz_permission_user">
						<?php _e('User', 'vz-secure-video'); ?>
					</label>
				</th>
				<td>
					<select name="vz_permission_user" id="vz_permission_user" style="width: 100%;">
						<option value=""><?php _e('Select a user...', 'vz-secure-video'); ?></option>
						<?php
						$users = get_users(array('orderby' => 'display_name'));
						foreach ($users as $user) {
							echo '<option value="' . esc_attr($user->ID) . '">' 
								. esc_html($user->display_name . ' (' . $user->user_email . ')') 
								. '</option>';
						}
						?>
					</select>
					<p class="description">
						<?php _e('Select a user to grant access to this video.', 'vz-secure-video'); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="vz_permission_view_limit">
						<?php _e('View Limit', 'vz-secure-video'); ?>
					</label>
				</th>
				<td>
					<input type="number" 
								 name="vz_permission_view_limit" 
								 id="vz_permission_view_limit" 
								 value="" 
								 min="1" 
								 placeholder="<?php _e('Unlimited', 'vz-secure-video'); ?>" />
					<p class="description">
						<?php _e('Number of times the user can view this video. Leave empty for unlimited views.', 'vz-secure-video'); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="vz_permission_expires_at">
						<?php _e('Expiration Date', 'vz-secure-video'); ?>
					</label>
				</th>
				<td>
					<input type="datetime-local" 
								 name="vz_permission_expires_at" 
								 id="vz_permission_expires_at" 
								 value="" />
					<p class="description">
						<?php _e('Optional: Set when this permission expires. Leave empty for no expiration.', 'vz-secure-video'); ?>
					</p>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<button type="button" 
									class="button button-primary" 
									id="vz_add_permission">
						<?php _e('Grant Access', 'vz-secure-video'); ?>
					</button>
				</td>
			</tr>
		</table>
	</div>

	<!-- Existing Permissions -->
	<div class="vz-existing-permissions">
		<h3><?php _e('Current Permissions', 'vz-secure-video'); ?></h3>
		
		<?php if (empty($permissions)): ?>
			<p><?php _e('No users have access to this video yet.', 'vz-secure-video'); ?></p>
		<?php else: ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e('User', 'vz-secure-video'); ?></th>
						<th><?php _e('View Limit', 'vz-secure-video'); ?></th>
						<th><?php _e('Views Used', 'vz-secure-video'); ?></th>
						<th><?php _e('Remaining', 'vz-secure-video'); ?></th>
						<th><?php _e('Expires', 'vz-secure-video'); ?></th>
						<th><?php _e('Granted', 'vz-secure-video'); ?></th>
						<th><?php _e('Actions', 'vz-secure-video'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($permissions as $permission): ?>
						<?php
						// Check if permission is expired
						$is_expired = false;
						$expires_display = __('Never', 'vz-secure-video');
						
						if ($permission->expires_at) {
							$expires_timestamp = strtotime($permission->expires_at);
							$expires_display = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $expires_timestamp);
							
							if (current_time('timestamp') > $expires_timestamp) {
								$is_expired = true;
								$expires_display = '<span style="color: #d63638;">' . __('Expired', 'vz-secure-video') . '</span>';
							}
						}
						?>
						<tr data-permission-id="<?php echo esc_attr($permission->id); ?>" <?php echo $is_expired ? 'style="opacity: 0.6;"' : ''; ?>>
							<td>
								<strong><?php echo esc_html($permission->display_name); ?></strong><br>
								<small><?php echo esc_html($permission->user_email); ?></small>
							</td>
							<td>
								<?php 
								echo $permission->view_limit === null 
									? '<span style="color: #46b450;">' . __('Unlimited', 'vz-secure-video') . '</span>' 
									: number_format($permission->view_limit); 
								?>
							</td>
							<td><?php echo number_format($permission->views_used); ?></td>
							<td>
								<?php 
								$remaining = $permission->view_limit === null 
									? null 
									: max(0, $permission->view_limit - $permission->views_used);
								echo $remaining === null 
									? '<span style="color: #46b450;">∞</span>' 
									: number_format($remaining); 
								?>
							</td>
							<td><?php echo $expires_display; ?></td>
							<td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($permission->granted_at))); ?></td>
							<td>
								<button type="button" 
												class="button button-small vz-revoke-permission" 
												data-post-id="<?php echo esc_attr($post_id); ?>"
												data-user-id="<?php echo esc_attr($permission->user_id); ?>">
									<?php _e('Revoke', 'vz-secure-video'); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

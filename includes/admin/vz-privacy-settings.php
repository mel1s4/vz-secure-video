<?php
/**
 * Privacy Settings Page
 * 
 * Handles GDPR compliance and privacy controls
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Add privacy settings page to WordPress admin menu
 */
function vz_privacy_settings_menu() {
	add_options_page(
		__('Viroz Secure Video Privacy', 'vz-secure-video'),
		__('Video Privacy', 'vz-secure-video'),
		'manage_options',
		'vz-video-privacy',
		'vz_privacy_settings_page'
	);
}

/**
 * Register privacy settings
 */
function vz_register_privacy_settings() {
	// Register GDPR settings
	register_setting(
		'vz_privacy_settings',
		'vz_gdpr_enabled',
		array(
			'type' => 'boolean',
			'default' => false,
			'sanitize_callback' => 'absint'
		)
	);
	
	register_setting(
		'vz_privacy_settings',
		'vz_require_explicit_consent',
		array(
			'type' => 'boolean',
			'default' => false,
			'sanitize_callback' => 'absint'
		)
	);
	
	// Register data collection settings
	register_setting(
		'vz_privacy_settings',
		'vz_track_ip',
		array(
			'type' => 'boolean',
			'default' => true,
			'sanitize_callback' => 'absint'
		)
	);
	
	register_setting(
		'vz_privacy_settings',
		'vz_anonymize_ip',
		array(
			'type' => 'boolean',
			'default' => false,
			'sanitize_callback' => 'absint'
		)
	);
	
	register_setting(
		'vz_privacy_settings',
		'vz_track_user_agent',
		array(
			'type' => 'boolean',
			'default' => false,
			'sanitize_callback' => 'absint'
		)
	);
	
	// Register data retention settings
	register_setting(
		'vz_privacy_settings',
		'vz_log_retention_days',
		array(
			'type' => 'integer',
			'default' => 365,
			'sanitize_callback' => 'absint'
		)
	);
	
	register_setting(
		'vz_privacy_settings',
		'vz_auto_cleanup_enabled',
		array(
			'type' => 'boolean',
			'default' => true,
			'sanitize_callback' => 'absint'
		)
	);
	
	// Register user rights settings
	register_setting(
		'vz_privacy_settings',
		'vz_allow_data_export',
		array(
			'type' => 'boolean',
			'default' => true,
			'sanitize_callback' => 'absint'
		)
	);
	
	register_setting(
		'vz_privacy_settings',
		'vz_allow_data_deletion',
		array(
			'type' => 'boolean',
			'default' => true,
			'sanitize_callback' => 'absint'
		)
	);
	
	register_setting(
		'vz_privacy_settings',
		'vz_privacy_policy_url',
		array(
			'type' => 'string',
			'default' => '',
			'sanitize_callback' => 'esc_url_raw'
		)
	);
}

/**
 * Render privacy settings page
 */
function vz_privacy_settings_page() {
	// Check user capabilities
	if (!current_user_can('manage_options')) {
		return;
	}
	
	// Show settings saved message
	if (isset($_GET['settings-updated'])) {
		add_settings_error(
			'vz_privacy_messages',
			'vz_privacy_message',
			__('Privacy settings saved successfully.', 'vz-secure-video'),
			'success'
		);
	}
	
	// Display any settings errors
	settings_errors('vz_privacy_messages');
	?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		
		<div class="vz-privacy-intro">
			<p><?php _e('Configure privacy and data protection settings for Viroz Secure Video. These settings help you comply with GDPR, CCPA, and other privacy regulations.', 'vz-secure-video'); ?></p>
		</div>
		
		<form method="post" action="options.php">
			<?php
			settings_fields('vz_privacy_settings');
			do_settings_sections('vz_privacy_settings');
			?>
			
			<h2 class="title"><?php _e('GDPR Compliance', 'vz-secure-video'); ?></h2>
			<p class="description"><?php _e('Enable GDPR compliance features if you serve users in the European Union.', 'vz-secure-video'); ?></p>
			
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="vz_gdpr_enabled"><?php _e('GDPR Compliance Mode', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<input 
							type="checkbox" 
							id="vz_gdpr_enabled" 
							name="vz_gdpr_enabled" 
							value="1" 
							<?php checked(get_option('vz_gdpr_enabled'), 1); ?>
						>
						<label for="vz_gdpr_enabled">
							<?php _e('Enable GDPR compliance mode', 'vz-secure-video'); ?>
						</label>
						<p class="description">
							<?php _e('When enabled, this activates GDPR-specific features including explicit consent requirements, data export/deletion capabilities, and enhanced privacy controls.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="vz_require_explicit_consent"><?php _e('Explicit Consent', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<input 
							type="checkbox" 
							id="vz_require_explicit_consent" 
							name="vz_require_explicit_consent" 
							value="1" 
							<?php checked(get_option('vz_require_explicit_consent'), 1); ?>
						>
						<label for="vz_require_explicit_consent">
							<?php _e('Require explicit user consent for data collection', 'vz-secure-video'); ?>
						</label>
						<p class="description">
							<?php _e('Users must explicitly opt-in before any personal data is collected. Recommended for GDPR compliance.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
			</table>
			
			<h2 class="title"><?php _e('Data Collection', 'vz-secure-video'); ?></h2>
			<p class="description"><?php _e('Control what data is collected from users. Collect only what you need for your specific use case.', 'vz-secure-video'); ?></p>
			
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="vz_track_ip"><?php _e('IP Address Tracking', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<input 
							type="checkbox" 
							id="vz_track_ip" 
							name="vz_track_ip" 
							value="1" 
							<?php checked(get_option('vz_track_ip'), 1); ?>
						>
						<label for="vz_track_ip">
							<?php _e('Track IP addresses', 'vz-secure-video'); ?>
						</label>
						<p class="description">
							<?php _e('IP addresses are used for security (preventing unauthorized access) and analytics. Required for security but can be anonymized.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="vz_anonymize_ip"><?php _e('Anonymize IP Addresses', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<input 
							type="checkbox" 
							id="vz_anonymize_ip" 
							name="vz_anonymize_ip" 
							value="1" 
							<?php checked(get_option('vz_anonymize_ip'), 1); ?>
						>
						<label for="vz_anonymize_ip">
							<?php _e('Anonymize IP addresses (last octet removed)', 'vz-secure-video'); ?>
						</label>
						<p class="description">
							<?php _e('Removes the last octet of IP addresses to reduce identifiability while maintaining security functionality. Recommended for GDPR compliance.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="vz_track_user_agent"><?php _e('User Agent Tracking', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<input 
							type="checkbox" 
							id="vz_track_user_agent" 
							name="vz_track_user_agent" 
							value="1" 
							<?php checked(get_option('vz_track_user_agent'), 1); ?>
						>
						<label for="vz_track_user_agent">
							<?php _e('Track user agents (browser/device info)', 'vz-secure-video'); ?>
						</label>
						<p class="description">
							<?php _e('User agent information helps with compatibility debugging and device analytics. Not required for basic video playback.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
			</table>
			
			<h2 class="title"><?php _e('Data Retention', 'vz-secure-video'); ?></h2>
			<p class="description"><?php _e('Automatically delete old data to reduce storage and comply with data minimization principles.', 'vz-secure-video'); ?></p>
			
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="vz_auto_cleanup_enabled"><?php _e('Automatic Cleanup', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<input 
							type="checkbox" 
							id="vz_auto_cleanup_enabled" 
							name="vz_auto_cleanup_enabled" 
							value="1" 
							<?php checked(get_option('vz_auto_cleanup_enabled'), 1); ?>
						>
						<label for="vz_auto_cleanup_enabled">
							<?php _e('Enable automatic data cleanup', 'vz-secure-video'); ?>
						</label>
						<p class="description">
							<?php _e('Automatically delete old view logs based on the retention period below.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="vz_log_retention_days"><?php _e('Data Retention Period', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<select id="vz_log_retention_days" name="vz_log_retention_days">
							<option value="0" <?php selected(get_option('vz_log_retention_days'), 0); ?>>
								<?php _e('Never delete', 'vz-secure-video'); ?>
							</option>
							<option value="30" <?php selected(get_option('vz_log_retention_days'), 30); ?>>
								<?php _e('30 days', 'vz-secure-video'); ?>
							</option>
							<option value="90" <?php selected(get_option('vz_log_retention_days'), 90); ?>>
								<?php _e('90 days', 'vz-secure-video'); ?>
							</option>
							<option value="180" <?php selected(get_option('vz_log_retention_days'), 180); ?>>
								<?php _e('180 days', 'vz-secure-video'); ?>
							</option>
							<option value="365" <?php selected(get_option('vz_log_retention_days'), 365); ?>>
								<?php _e('1 year', 'vz-secure-video'); ?>
							</option>
							<option value="730" <?php selected(get_option('vz_log_retention_days'), 730); ?>>
								<?php _e('2 years', 'vz-secure-video'); ?>
							</option>
						</select>
						<p class="description">
							<?php _e('View logs older than this period will be automatically deleted. Recommended: 90-365 days for most use cases.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
			</table>
			
			<h2 class="title"><?php _e('User Rights', 'vz-secure-video'); ?></h2>
			<p class="description"><?php _e('Enable users to exercise their privacy rights under GDPR and other regulations.', 'vz-secure-video'); ?></p>
			
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="vz_allow_data_export"><?php _e('Data Export', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<input 
							type="checkbox" 
							id="vz_allow_data_export" 
							name="vz_allow_data_export" 
							value="1" 
							<?php checked(get_option('vz_allow_data_export'), 1); ?>
						>
						<label for="vz_allow_data_export">
							<?php _e('Allow users to export their data', 'vz-secure-video'); ?>
						</label>
						<p class="description">
							<?php _e('Users can request a copy of all their data in JSON or CSV format. Required for GDPR compliance.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="vz_allow_data_deletion"><?php _e('Data Deletion', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<input 
							type="checkbox" 
							id="vz_allow_data_deletion" 
							name="vz_allow_data_deletion" 
							value="1" 
							<?php checked(get_option('vz_allow_data_deletion'), 1); ?>
						>
						<label for="vz_allow_data_deletion">
							<?php _e('Allow users to delete their data', 'vz-secure-video'); ?>
						</label>
						<p class="description">
							<?php _e('Users can request deletion of all their data ("right to be forgotten"). Required for GDPR compliance.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="vz_privacy_policy_url"><?php _e('Privacy Policy URL', 'vz-secure-video'); ?></label>
					</th>
					<td>
						<input 
							type="url" 
							id="vz_privacy_policy_url" 
							name="vz_privacy_policy_url" 
							value="<?php echo esc_attr(get_option('vz_privacy_policy_url')); ?>" 
							class="regular-text"
							placeholder="https://example.com/privacy-policy"
						>
						<p class="description">
							<?php _e('Link to your privacy policy page. This will be shown to users when they request data export or deletion.', 'vz-secure-video'); ?>
						</p>
					</td>
				</tr>
			</table>
			
			<?php submit_button(__('Save Privacy Settings', 'vz-secure-video')); ?>
		</form>
		
		<div class="vz-privacy-footer">
			<h2><?php _e('Privacy Best Practices', 'vz-secure-video'); ?></h2>
			<ul>
				<li><?php _e('✓ Collect only the data you need for your specific use case', 'vz-secure-video'); ?></li>
				<li><?php _e('✓ Use IP anonymization if you don\'t need full IP addresses', 'vz-secure-video'); ?></li>
				<li><?php _e('✓ Set appropriate data retention periods (90-365 days recommended)', 'vz-secure-video'); ?></li>
				<li><?php _e('✓ Enable GDPR mode if you serve EU users', 'vz-secure-video'); ?></li>
				<li><?php _e('✓ Provide a clear privacy policy explaining data collection', 'vz-secure-video'); ?></li>
				<li><?php _e('✓ Enable automatic cleanup to reduce storage costs', 'vz-secure-video'); ?></li>
			</ul>
		</div>
		
		<div class="vz-privacy-legal-notice">
			<h2><?php _e('Legal Disclaimer', 'vz-secure-video'); ?></h2>
			<p>
				<?php _e('This plugin provides tools to help with privacy compliance, but it does not constitute legal advice. You are responsible for ensuring your site complies with all applicable privacy laws (GDPR, CCPA, etc.). We recommend consulting with a legal professional to ensure full compliance.', 'vz-secure-video'); ?>
			</p>
		</div>
	</div>
	
	<style>
		.vz-privacy-intro {
			background: #fff;
			border: 1px solid #c3c4c7;
			border-left: 4px solid #2271b1;
			padding: 12px;
			margin: 20px 0;
		}
		
		.vz-privacy-footer {
			background: #f0f6fc;
			border: 1px solid #c3c4c7;
			padding: 20px;
			margin: 30px 0;
		}
		
		.vz-privacy-footer h2 {
			margin-top: 0;
		}
		
		.vz-privacy-footer ul {
			list-style: none;
			padding-left: 0;
		}
		
		.vz-privacy-footer li {
			padding: 8px 0;
			font-size: 14px;
		}
		
		.vz-privacy-legal-notice {
			background: #fff3cd;
			border: 1px solid #ffc107;
			border-left: 4px solid #ffc107;
			padding: 15px;
			margin: 20px 0;
		}
		
		.vz-privacy-legal-notice h2 {
			margin-top: 0;
			color: #856404;
		}
		
		.vz-privacy-legal-notice p {
			color: #856404;
			margin-bottom: 0;
		}
	</style>
	<?php
}

/**
 * Helper function to check if GDPR is enabled
 */
function vz_is_gdpr_enabled() {
	return (bool) get_option('vz_gdpr_enabled', false);
}

/**
 * Helper function to check if IP tracking is enabled
 */
function vz_is_ip_tracking_enabled() {
	return (bool) get_option('vz_track_ip', true);
}

/**
 * Helper function to check if IP should be anonymized
 */
function vz_should_anonymize_ip() {
	return (bool) get_option('vz_anonymize_ip', false);
}

/**
 * Helper function to anonymize IP address
 */
function vz_anonymize_ip_address($ip) {
	if (!vz_should_anonymize_ip()) {
		return $ip;
	}
	
	// Remove last octet for IPv4
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		return preg_replace('/\.\d+$/', '.0', $ip);
	}
	
	// Remove last 4 groups for IPv6
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
		$parts = explode(':', $ip);
		$parts = array_slice($parts, 0, -4);
		return implode(':', $parts) . ':0:0:0:0';
	}
	
	return $ip;
}

/**
 * Helper function to check if user agent tracking is enabled
 */
function vz_is_user_agent_tracking_enabled() {
	return (bool) get_option('vz_track_user_agent', false);
}

/**
 * Helper function to get the configured retention period
 */
function vz_get_log_retention_days() {
	return absint(get_option('vz_log_retention_days', 365));
}

/**
 * Helper function to check if auto cleanup is enabled
 */
function vz_is_auto_cleanup_enabled() {
	return (bool) get_option('vz_auto_cleanup_enabled', true);
}

// Hook into WordPress
add_action('admin_menu', 'vz_privacy_settings_menu');
add_action('admin_init', 'vz_register_privacy_settings');


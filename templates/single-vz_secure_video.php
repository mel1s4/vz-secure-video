<?php
/**
 * Template for displaying single secure video posts
 * 
 * This template overrides the default single post template
 * and displays a custom video player page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Get the post
global $post;

// Check if user has permission to view this video
if (!vz_user_can_view_video(get_the_ID())) {
		// Get redirect URL from post meta or use default
		$redirect_url = get_post_meta(get_the_ID(), '_vz_redirect_no_access', true);
		
		if (!$redirect_url) {
				$redirect_url = home_url();
		}
		
		// Get custom message
		$message = get_post_meta(get_the_ID(), '_vz_no_access_message', true);
		if (!$message) {
				$message = __('You do not have permission to view this video.', 'vz-secure-video');
		}
		
		// Check remaining views
		$remaining = vz_get_remaining_views(get_the_ID());
		
		if ($remaining !== null) {
				if ($remaining === 0) {
						$message = __('You have reached your view limit for this video.', 'vz-secure-video');
				} else {
						$message .= ' ' . sprintf(
								__('You have %d view(s) remaining.', 'vz-secure-video'),
								$remaining
						);
				}
		}
		
		// Display error message
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
				<meta charset="<?php bloginfo('charset'); ?>">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title><?php the_title(); ?> - <?php bloginfo('name'); ?></title>
				<style>
						body {
								font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
								display: flex;
								align-items: center;
								justify-content: center;
								min-height: 100vh;
								margin: 0;
								background: #f0f0f1;
						}
						.error-container {
								background: white;
								padding: 40px;
								border-radius: 8px;
								box-shadow: 0 2px 8px rgba(0,0,0,0.1);
								max-width: 500px;
								text-align: center;
						}
						.error-icon {
								font-size: 64px;
								color: #d63638;
								margin-bottom: 20px;
						}
						h1 {
								color: #1d2327;
								margin: 0 0 15px 0;
						}
						p {
								color: #646970;
								margin: 0 0 25px 0;
								line-height: 1.6;
						}
						.button {
								display: inline-block;
								padding: 12px 24px;
								background: #2271b1;
								color: white;
								text-decoration: none;
								border-radius: 4px;
								transition: background 0.2s;
						}
						.button:hover {
								background: #135e96;
						}
				</style>
		</head>
		<body>
				<div class="error-container">
						<div class="error-icon">🔒</div>
						<h1><?php _e('Access Denied', 'vz-secure-video'); ?></h1>
						<p><?php echo esc_html($message); ?></p>
						<a href="<?php echo esc_url($redirect_url); ?>" class="button">
								<?php _e('Go Back', 'vz-secure-video'); ?>
						</a>
				</div>
		</body>
		</html>
		<?php
		exit;
}

// User has permission, continue to video player
get_header();
?>

<!-- Load the video player template -->
<?php
include VZ_SECURE_VIDEO_PLUGIN_DIR . 'vz-video-player.php';
?>

<?php
get_footer();


<?php
/**
 * Template for Video Resources Meta Box
 * 
 * @var int $post_id Post ID
 * @var int $zip_file_id Attachment ID of the selected ZIP file
 * @var string $zip_file_url URL of the selected ZIP file
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Get video file info
$video_file_id = get_post_meta(
		$post_id,
		'_vz_secure_video_video_file',
		true
);
$video_file_url = '';
$video_file_type = '';

if ($video_file_id) {
		$video_file_url = wp_get_attachment_url($video_file_id);
		$video_file_type = get_post_mime_type($video_file_id);
}
?>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="vz_secure_video_video_file">
				<?php _e('Video File', 'vz-secure-video'); ?>
			</label>
		</th>
		<td>
			<input type="hidden"
						 id="vz_secure_video_video_file"
						 name="vz_secure_video_video_file"
						 value="<?php echo esc_attr($video_file_id); ?>"
						 class="vz-secure-video-video-file" />
			<div id="vz_video_file_preview"
					 style="margin-bottom: 10px;">
				<?php if ($video_file_url): ?>
					<p>
						<strong><?php _e('Selected file:', 'vz-secure-video'); ?></strong>
						<a href="<?php echo esc_url($video_file_url); ?>"
							 target="_blank">
								<?php echo basename($video_file_url); ?>
						</a>
						<span class="vz-secure-video-video-file-type">
								<?php echo esc_html($video_file_type); ?>
						</span>
					</p>
					<button type="button"
									class="button"
									id="vz_remove_video_file">
							<?php _e('Remove File', 'vz-secure-video'); ?>
					</button>
				<?php endif; ?>
			</div>
			<button type="button"
							class="button"
							id="vz_select_video_file">
					<?php _e('Select Video File', 'vz-secure-video'); ?>
			</button>
			<p class="description">
					<?php
					_e(
							'Select a video file (MP4, WebM, OGG) or HLS ZIP archive '
							. 'from the media library.',
							'vz-secure-video'
					);
					?>
			</p>
		</td>
	</tr>
</table>

<table class="form-table"
			 style="margin-top: 20px;">
	<tr>
		<th scope="row">
			<label for="vz_secure_video_zip_file">
					<?php _e('HLS ZIP File (Legacy)', 'vz-secure-video'); ?>
			</label>
		</th>
		<td>
			<input type="hidden"
						 id="vz_secure_video_zip_file"
						 name="vz_secure_video_zip_file"
						 value="<?php echo esc_attr($zip_file_id); ?>" />
			<div id="vz_zip_file_preview"
					 style="margin-bottom: 10px;">
				<?php if ($zip_file_url): ?>
					<p>
							<strong>
									<?php _e('Selected file:', 'vz-secure-video'); ?>
							</strong>
							<a href="<?php echo esc_url($zip_file_url); ?>"
								 target="_blank">
									<?php echo basename($zip_file_url); ?>
							</a>
					</p>
					<button type="button"
									class="button"
									id="vz_remove_zip_file">
							<?php _e('Remove File', 'vz-secure-video'); ?>
					</button>
				<?php endif; ?>
			</div>
			<button type="button"
							class="button"
							id="vz_select_zip_file">
					<?php _e('Select ZIP File', 'vz-secure-video'); ?>
			</button>
			<p class="description">
					<?php
					_e(
							'Select a ZIP file containing HLS video files '
							. '(for advanced streaming).',
							'vz-secure-video'
					);
					?>
			</p>
		</td>
	</tr>
</table>


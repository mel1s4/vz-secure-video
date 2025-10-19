<?php
/**
 * Template for Large File Upload Admin Notice
 * 
 * @var string $install_plugins_url URL to the recommended plugins page
 * @var string $nonce Nonce for AJAX dismissal
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="notice notice-info is-dismissible
            vz-secure-video-large-file-notice">
  <p>
    <strong>
        <?php _e('Large File Uploads', 'vz-secure-video'); ?>
    </strong><br>
    <?php
    _e(
        'You can upload large ZIP files with this plugin. '
        . 'For best results with very large files, we recommend installing:',
        'vz-secure-video'
    );
    ?>
    <a href="<?php echo esc_url($install_plugins_url); ?>">
      <strong>
          <?php _e('Big File Uploads', 'vz-secure-video'); ?>
      </strong>
    </a>
    <?php _e('or', 'vz-secure-video'); ?>
    <a href="<?php echo esc_url($install_plugins_url); ?>">
      <strong>
          <?php _e('WP Maximum Upload File Size', 'vz-secure-video'); ?>
      </strong>
    </a>
  </p>
</div>

<script>
jQuery(document).ready(function($) {
  $(document).on(
    'click',
    '.vz-secure-video-large-file-notice .notice-dismiss',
    function() {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'vz_secure_video_dismiss_large_file_notice',
          nonce: '<?php echo esc_js($nonce); ?>'
            }
          });
        }
    );
});
</script>


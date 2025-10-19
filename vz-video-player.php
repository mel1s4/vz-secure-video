<?php
  $assets_folder = plugin_dir_url(__FILE__) . 'video-player/dist/assets/';
  $js_file = '';
  $css_file = '';
  $assets_folder_files = scandir(__DIR__ . '/video-player/dist/assets/');
  $fonts = [];
  foreach ($assets_folder_files as $file) {
    if (strpos($file, '.js') !== false) {
      $js_file = $file;
    }
    if (strpos($file, '.css') !== false) {
      $css_file = $file;
    }
    if (strpos($file, '.woff') !== false || strpos($file, '.woff2') !== false) {
      $fonts[] = $file;
    }
  }
  $post_id = get_the_ID();
  
  // Get video source (supports both HLS and direct video files)
  $video_source = vz_get_secure_video_source($post_id);
  $file_url = $video_source ? $video_source['url'] : '';
  $file_type = $video_source ? $video_source['type'] : '';
  $is_hls = vz_is_secure_video_hls($post_id);
  
  $tags = get_tags();
  $categories = get_categories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script type="module" crossorigin src="<?php echo esc_url($assets_folder . $js_file); ?>"></script>
  <script>
    // Make ajaxurl available for AJAX requests
    window.ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
  </script>
  <link rel="stylesheet" crossorigin href="<?php echo esc_url($assets_folder . $css_file); ?>">
  <script>
    window.vzVideoData = {
      title: '<?php the_title(); ?>',	
      description: '<?php the_excerpt(); ?>',
      thumbnail: '<?php the_post_thumbnail_url(); ?>',
      file: '<?php echo esc_js($file_url); ?>',
      fileType: '<?php echo esc_js($file_type); ?>',
      isHls: <?php echo $is_hls ? 'true' : 'false'; ?>,
      postId: '<?php the_ID(); ?>',
      viewTrackingNonce: '<?php echo wp_create_nonce('vz_track_view'); ?>',
      ajaxUrl: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
      videoViews: '<?php echo vz_get_video_view_count($post_id); ?>',
      duration: '<?php $duration ?>',
      tags: '<?php $tags ?>',
      categories: '<?php $categories ?>',
    };
  </script>
  <title>Video Player</title>
</head>
<body>
  <div id="root"></div>
</body>
</html>

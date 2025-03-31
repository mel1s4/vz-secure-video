<?php 
  $base_url = get_bloginfo('url');
  $id = get_the_ID();
  $media_id = get_post_meta( $id, '_vz_secure_video_file', true );
  $upload_dir = wp_upload_dir();
  
  if ( empty( $media_id ) ) {
    $media_filepath = get_post_meta( $id, '_vz_secure_video_filepath', true );
    $destination_path = $upload_dir['basedir'] . '/vz-secure_video/' . $media_filepath;
    // get first file m3u8 inside the destination_path
    $first_file = glob($destination_path . '/*.m3u8');
    $first_file = basename($first_file[0]);
    $video = $upload_dir['baseurl'] . '/vz-secure_video/' . $media_filepath . '/' . $first_file;
  } else {
    $name = get_the_title( $media_id );
    $secure_video_folder = '/vz-secure_video/' . $media_id;
    $destination_path = $upload_dir['basedir'] . $secure_video_folder;
    // get the firs folder inside destination_path
    $first_folder = glob($destination_path . '/*' , GLOB_ONLYDIR);
    $first_folder = basename($first_folder[0]);
    $first_file = glob($destination_path . '/' . $first_folder . '/*.m3u8');
    $first_file = basename($first_file[0]);
    $video = $upload_dir['baseurl'] . $secure_video_folder . '/' . $first_folder . '/' . $first_file;
  }

  // get the first file .m3u8 inside the first folder
  $plugin_url = plugin_dir_url( __FILE__ );
  $styles = $plugin_url . 'player.css';
  $js = $plugin_url . 'js/frontend.js';

  if (!vz_sv_current_user_can_view($id)) {
    // Redirect to the home page
    wp_redirect( home_url() );
    exit;
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> <?php the_title() ?> </title>
  <link rel="stylesheet" href="<?php echo $styles; ?>">
</head>
<body>
<video id="player" controls>
    <source src="<?php echo $video; ?>" type="application/x-mpegURL">
</video>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script src="<?php echo $js; ?>"></script>
<script>
  var video = document.getElementById('player');
  if(Hls.isSupported()) {
    var hls = new Hls();
    hls.loadSource('<?php echo $video; ?>');
    hls.attachMedia(video);
  }
  else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    video.src = "<?php echo $video; ?>";
    video.addEventListener('loadedmetadata',function() {
        video.play();
    });
  } else {
    console.error('HLS not supported');
  }
</script>
</body>
</html>
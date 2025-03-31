<?php
 $value = get_post_meta( $post->ID, '_vz_secure_video_file', true );
 $filepath = get_post_meta( $post->ID, '_vz_secure_video_filepath', true );
 $name = '';
 if ( ! empty( $value ) ) {
   $name = get_the_title( $value );
 }
 ?>
 <div class="wrap">
 <label for="vz_secure_video_file">
    <?php _e( 'Set video filepath:', 'vz-secure-video' ); ?>
   </label>
    <input type="text" id="vz_secure_video_filepath" name="vz_secure_video_filepath" value="<?php echo esc_attr( $filepath ); ?>" />
   <label for="vz_secure_video_file">
    <?php _e( 'Upload a secure video file:', 'vz-secure-video' ); ?>
   </label>
  <input type="text" id="vz_secure_video_file_input" name="vz_secure_video_file" value="<?php echo esc_attr( $value ); ?>" size="25" />
  <input type="text" id="vz_secure_video_file_name" value="<?php echo esc_attr( $name ); ?>" size="25" />
  <input type="button" id="vz_secure_video_file_button" class="button" value="<?php esc_attr_e( 'Upload Video', 'vz-secure-video' ); ?>" />

  <div class="give-permissions">
    <label for="vz_secure_video_user_search">
      <?php _e( 'User:', 'vz-secure-video' ); ?>
    </label>
    <input type="text" 
           id="vz_secure_video_user" 
           name="vz_secure_video_user" 
           placeholder="<?php esc_attr_e( 'Enter username or email', 'vz-secure-video' ); ?>" 
           size="25" />
    <label>
      <?php _e( 'Days to view:', 'vz-secure-video' ); ?>
    </label>
    <input type="number" id="vz_secure_video_permission_days" name="vz_secure_video_permission_days" value="3" />
  </div>
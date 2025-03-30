<?php 
/*
Plugin Name: Viroz Secure Video
Plugin URI: https://viroz.studio/project/vz-secure-video
Description: Lets admins upload videos to the media library and restricts access to them.
Version: 0.1.0
Author: Melisa Viroz
Author URI: http://melisaviroz.com
License: GPL2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// Function to execute on plugin activation
function vz_secure_video_activate() {
  // Ensure the custom post type is registered before flushing rewrite rules
  vz_register_secure_video_post_type();
  vz_register_secure_video_category_taxonomy();

  // Flush rewrite rules to ensure custom post type and taxonomy work
  flush_rewrite_rules();

  // create a table if it doesnt exist for 'vz_secure-video-permissions'
  global $wpdb;
  $table_name = $wpdb->prefix . 'vz_secure_video_permissions';
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    video_id bigint(20) NOT NULL,
    creation datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    expiration datetime DEFAULT NULL,
    PRIMARY KEY  (id)
  ) $charset_collate;";
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
}
register_activation_hook( __FILE__, 'vz_secure_video_activate' );

// Function to execute on plugin deactivation
function vz_secure_video_deactivate() {
  // Flush rewrite rules to remove custom post type and taxonomy
  flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'vz_secure_video_deactivate' );

// Function to execute on plugin uninstall
function vz_secure_video_uninstall() {
  // Flush rewrite rules to remove custom post type and taxonomy
  flush_rewrite_rules();

  // Optionally, you can drop the table if you want to clean up on uninstall
  global $wpdb;
  $table_name = $wpdb->prefix . 'vz_secure_video_permissions';
  $sql = "DROP TABLE IF EXISTS $table_name;";
  $wpdb->query( $sql );
}

// Register Custom Post Type: Secure Video
function vz_register_secure_video_post_type() {
  $labels = array(
    'name'                  => _x( 'Secure Videos', 'Post Type General Name', 'vz-secure-video' ),
    'singular_name'         => _x( 'Secure Video', 'Post Type Singular Name', 'vz-secure-video' ),
    'menu_name'             => __( 'Secure Videos', 'vz-secure-video' ),
    'name_admin_bar'        => __( 'Secure Video', 'vz-secure-video' ),
    'add_new_item'          => __( 'Add New Secure Video', 'vz-secure-video' ),
    'edit_item'             => __( 'Edit Secure Video', 'vz-secure-video' ),
    'new_item'              => __( 'New Secure Video', 'vz-secure-video' ),
    'view_item'             => __( 'View Secure Video', 'vz-secure-video' ),
    'search_items'          => __( 'Search Secure Videos', 'vz-secure-video' ),
    'not_found'             => __( 'No Secure Videos found', 'vz-secure-video' ),
    'not_found_in_trash'    => __( 'No Secure Videos found in Trash', 'vz-secure-video' ),
  );

  $args = array(
    'label'                 => __( 'Secure Video', 'vz-secure-video' ),
    'labels'                => $labels,
    'supports'              => array( 'title', 'editor', 'thumbnail' ),
    'public'                => true,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'menu_position'         => 20,
    'menu_icon'             => 'dashicons-video-alt2',
    'capability_type'       => 'post',
    'has_archive'           => false,
    'exclude_from_search'   => true,
    'publicly_queryable'    => true,
  );

  register_post_type( 'secure_video', $args );
}
add_action( 'init', 'vz_register_secure_video_post_type' );

// Register Custom Taxonomy: Secure Video Category

function vz_register_secure_video_category_taxonomy() {
  // check if secure folder exists
  $upload_dir = wp_upload_dir();
  $secure_video_folder = $upload_dir['basedir'] . '/vz-secure_video/';
  if ( ! file_exists( $secure_video_folder ) ) {
    mkdir( $secure_video_folder, 0755, true );
    // make a blank index.html file
    $index_file = fopen( $secure_video_folder . 'index.html', 'w' );
    if ( $index_file ) {
      fwrite( $index_file, '<h1>Access Denied</h1>' );
      fclose( $index_file );
    }
    // make a .htaccess file such that it denies access to the folder 
    // but allows access to the index.html file
    $htaccess_file = fopen( $secure_video_folder . '.htaccess', 'w' );
    if ( $htaccess_file ) {
      fwrite( $htaccess_file, 'Options -Indexes' . PHP_EOL );
      fwrite( $htaccess_file, 'RewriteEngine On' . PHP_EOL );
      fwrite( $htaccess_file, 'RewriteCond %{REQUEST_FILENAME} !-f' . PHP_EOL );
      fwrite( $htaccess_file, 'RewriteCond %{REQUEST_FILENAME} !-d' . PHP_EOL );
      fwrite( $htaccess_file, 'RewriteRule ^index\.html$ - [L]' . PHP_EOL );
      fclose( $htaccess_file );
    }
  }
  $labels = array(
    'name'                       => _x( 'Video Categories', 'Taxonomy General Name', 'vz-secure-video' ),
    'singular_name'              => _x( 'Video Category', 'Taxonomy Singular Name', 'vz-secure-video' ),
    'menu_name'                  => __( 'Video Categories', 'vz-secure-video' ),
    'all_items'                  => __( 'All Secure Video Categories', 'vz-secure-video' ),
    'parent_item'                => __( 'Parent Secure Video Category', 'vz-secure-video' ),
    'parent_item_colon'          => __( 'Parent Secure Video Category:', 'vz-secure-video' ),
    'new_item_name'              => __( 'New Secure Video Category Name', 'vz-secure-video' ),
    'add_new_item'               => __( 'Add New Secure Video Category', 'vz-secure-video' ),
    'edit_item'                  => __( 'Edit Secure Video Category', 'vz-secure-video' ),
    'update_item'                => __( 'Update Secure Video Category', 'vz-secure-video' ),
    'view_item'                  => __( 'View Secure Video Category', 'vz-secure-video' ),
    'separate_items_with_commas' => __( 'Separate secure video categories with commas', 'vz-secure-video' ),
    'add_or_remove_items'        => __( 'Add or remove secure video categories', 'vz-secure-video' ),
    'choose_from_most_used'      => __( 'Choose from the most used secure video categories', 'vz-secure-video' ),
  );

  $args = array(
    'labels'                     => $labels,
    // Hierarchical taxonomy (like categories)
    // Non-hierarchical taxonomy (like tags)
    // Hierarchical taxonomy (like categories)
    // Non-hierarchical taxonomy (like tags)
    'hierarchical'               => true,
    'public'                     => false,
    'show_ui'                    => true,
    'show_admin_column'          => true,
    'query_var'                  => true,
    'rewrite'                    => array( 'slug' => 'vz_secure_video_category' ),
    'capabilities'               => array(
      'manage_terms' => 'manage_secure_video_categories',
      'edit_terms'   => 'edit_secure_video_categories',
      'delete_terms' => 'delete_secure_video_categories',
      'assign_terms' => 'assign_secure_video_categories',
    ),
    'show_in_rest'               => true,
    'rest_base'                  => 'secure_video_categories',
    'rest_controller_class'      => 'WP_REST_Terms_Controller',
    'show_in_graphql'            => true,
    'graphql_single_name'        => 'SecureVideoCategory',
    'graphql_plural_name'        => 'SecureVideoCategories',
  );
  register_taxonomy( 'secure_video_category', array( 'secure_video' ), $args );
}
add_action( 'init', 'vz_register_secure_video_category_taxonomy' );
// Add custom capabilities for the secure video post type

// Add custom meta box for file upload
function vz_add_secure_video_meta_box() {
  add_meta_box(
    'vz_secure_video_file',
    __( 'Secure Video File', 'vz-secure-video' ),
    'vz_secure_video_file_meta_box_callback',
    'secure_video',
    'normal',
    'high'
  );
}
add_action( 'add_meta_boxes', 'vz_add_secure_video_meta_box' );

function vz_secure_video_file_meta_box_callback( $post ) {
  wp_nonce_field( 'vz_secure_video_file_nonce', 'vz_secure_video_file_nonce' );
  include plugin_dir_path( __FILE__ ) . 'edit-secure-video.php';
}

// Enqueue media uploader scripts
function vz_enqueue_media_uploader() {
  wp_enqueue_media();
  wp_enqueue_script( 'vz-secure-video-media-uploader', plugin_dir_url( __FILE__ ) . 'js/media-uploader.js', array( 'jquery' ), null, true );
}
add_action( 'admin_enqueue_scripts', 'vz_enqueue_media_uploader' );

// enqueue styles for admin
function vz_enqueue_admin_styles() {
  wp_enqueue_style( 'vz-secure-video-admin', plugin_dir_url( __FILE__ ) . 'admin-styles.css' );
}
add_action( 'admin_enqueue_scripts', 'vz_enqueue_admin_styles' );


function vz_save_secure_video_file_meta_box_data( $post_id ) {
  if ( ! isset( $_POST['vz_secure_video_file_nonce'] ) ) {
    return;
  }

  if ( ! wp_verify_nonce( $_POST['vz_secure_video_file_nonce'], 'vz_secure_video_file_nonce' ) ) {
    return;
  }

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  if ( isset( $_POST['post_type'] ) && 'secure_video' === $_POST['post_type'] ) {
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
      return;
    }
  }

  if (isset( $_POST['vz_secure_video_file'] ) ) {
    $media_id = sanitize_text_field( $_POST['vz_secure_video_file'] );
    if ( empty( $media_id ) ) {
      delete_post_meta( $post_id, '_vz_secure_video_file' );
    } else {
      update_post_meta( $post_id, '_vz_secure_video_file', $media_id );
      $secure_video_folder = '/vz-secure_video/' . $media_id . '/';
      $upload_dir = wp_upload_dir();
      $destination_path = $upload_dir['basedir'] . $secure_video_folder;
      if ( ! file_exists( $upload_dir['basedir'] . $secure_video_folder ) ) {
        $media_path = get_attached_file( $media_id );
        $media_name = get_the_title( $media_id );
        mkdir( $upload_dir['basedir'] . $secure_video_folder, 0755, true );
        // unzip the file
        $zip = new ZipArchive;
        if ( $zip->open( $media_path ) === TRUE ) {
          $zip->extractTo( $destination_path );
          $zip->close();
        } else {
          // error unzipping the file
          error_log( 'Error unzipping the file: ' . $media_path );
        }
      }
    }
  }

  //   vz_secure_video_user
  // vz_secure_video_permission_days
  if(isset( $_POST['vz_secure_video_user'] ) ) {
    $user = sanitize_text_field( $_POST['vz_secure_video_user'] );
    // get the user id from the username or email
    $user_id = 0;
    if ( is_numeric( $user ) ) {
      $user_id = $user;
    } else {
      $user_data = get_user_by( 'login', $user );
      if ( ! $user_data ) {
        $user_data = get_user_by( 'email', $user );
      }
      if ( $user_data ) {
        $user_id = $user_data->ID;
      }
    }
    if ( $user_id ) {
      global $wpdb;
      $table_name = $wpdb->prefix . 'vz_secure_video_permissions';
      $expiration = date( 'Y-m-d H:i:s', strtotime( '+' . $_POST['vz_secure_video_permission_days'] . ' days' ) );
      $wpdb->insert( 
        $table_name, 
        array( 
          'user_id' => $user_id, 
          'video_id' => $post_id, 
          'expiration' => $expiration,
        ) 
      );
    }
  }

}
add_action( 'save_post', 'vz_save_secure_video_file_meta_box_data' );

// if the page is the single of a secure video show "single-vz-secure-video.php" template
function vz_secure_video_template( $template ) {
  if ( is_singular( 'secure_video' ) ) {
    return plugin_dir_path( __FILE__ ) . 'single-vz-secure-video.php';
  }
  return $template;
}
add_filter( 'single_template', 'vz_secure_video_template' );

function vz_sv_current_user_can_view( $secure_video_id ) {
  if ( current_user_can( 'manage_options' ) ) {
    return true;
  }
  if ( is_user_logged_in() ) {
    $user_id = get_current_user_id();
    return vz_sv_user_can_view( $user_id, $secure_video_id );
  }
  return false;
}

function vz_sv_user_can_view($user_id, $secure_video_id) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'vz_secure_video_permissions';
  $sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE user_id = %d AND video_id = %d ORDER BY creation DESC LIMIT 1", $user_id, $secure_video_id );
  $result = $wpdb->get_row( $sql );
  if ( ! empty( $result ) ) {
    // check if the expiration date is in the future
    if ( strtotime( $result->expiration ) > time() ) {
      return true;
    }
  }
  return false;
}


// check if woocommerce is active
function vz_is_woocommerce_active() {
  return class_exists( 'WooCommerce' );
}

// Add custom field to WooCommerce virtual products
function vz_add_video_access_field() {
  global $post;
  if ( 'product' === $post->post_type ) {
    $product = wc_get_product( $post->ID );

    if ( $product && $product->is_virtual() ) {
      $vz_secure_videos = get_posts( array(
        'post_type'   => 'secure_video',
        'numberposts' => -1,
      ) );
      $options = array();
      $options[ '' ] = __( 'Select a secure video', 'vz-secure-video' );
      foreach ( $vz_secure_videos as $video ) {
        $options[ $video->ID ] = $video->post_title;
      }
    
      woocommerce_wp_select( array(
        'id'          => '_vz_video_access',
        'label'       => __( 'Video to Access', 'vz-secure-video' ),
        'description' => __( 'Select the secure video to grant access to.', 'vz-secure-video' ),
        'desc_tip'    => true,
        'options'     => $options,
      ) );

      woocommerce_wp_text_input( array(
        'id'          => '_vz_video_access_lifetime',
        'label'       => __( 'Access Lifetime (days)', 'vz-secure-video' ),
        'description' => __( 'Enter the number of days the user will have access to the video.', 'vz-secure-video' ),
        'desc_tip'    => true,
        'type'        => 'number',
        'custom_attributes' => array(
          'min' => '1',
          'step' => '1',
        ),
      ) );
    }
  }
}
add_action( 'woocommerce_product_options_general_product_data', 'vz_add_video_access_field' );

// Save the custom field value
function vz_save_video_access_field( $post_id ) {
  $product = wc_get_product( $post_id );

  if ( $product && $product->is_virtual() ) {
    if ( isset( $_POST['_vz_video_access'] ) ) {
      $video_access = sanitize_text_field( $_POST['_vz_video_access'] );
      $product->update_meta_data( '_vz_video_access', $video_access );
    }

    if ( isset( $_POST['_vz_video_access_lifetime'] ) ) {
      $video_access_lifetime = intval( $_POST['_vz_video_access_lifetime'] );
      $product->update_meta_data( '_vz_video_access_lifetime', $video_access_lifetime );
    }

    $product->save();
  }
}
add_action( 'woocommerce_process_product_meta', 'vz_save_video_access_field' );

// check if woocommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  add_action('woocommerce_order_status_completed', 'vz_sv_order_completed', 20, 2);
}

function vz_sv_order_completed( $order_id, $order ) {
  // get the order items
  $items = $order->get_items();
  foreach ( $items as $item ) {
    // get the product id
    $product_id = $item->get_product_id();
    // get the video access id
    $video_access = get_post_meta( $product_id, '_vz_video_access', true );
    $acces_lifetime = get_post_meta( $product_id, '_vz_video_access_lifetime', true );
    // get the expiration date
    if ( ! empty( $video_access ) && ! empty( $acces_lifetime ) ) {
      // add the user to the video access table
      global $wpdb;
      $table_name = $wpdb->prefix . 'vz_secure_video_permissions';
      $user_id = $order->get_user_id();
      $expiration = date( 'Y-m-d H:i:s', strtotime( '+' . $acces_lifetime . ' days' ) );
      $wpdb->insert( 
        $table_name, 
        array( 
          'user_id' => $user_id, 
          'video_id' => $video_access, 
          'expiration' => $expiration,
        ) 
      );
    }
  }
}


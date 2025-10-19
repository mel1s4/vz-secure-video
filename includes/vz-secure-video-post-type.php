<?php
/**
 * Post Type Registration Functions
 * 
 * Handles registration of custom post types and taxonomies
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
		exit;
}

/**
 * Register Secure Video custom post type
 */
function vz_secure_video_register_post_type() {
		$labels = array(
				'name'                  => _x(
						'Secure Videos',
						'Post Type General Name',
						'vz-secure-video'
				),
				'singular_name'         => _x(
						'Secure Video',
						'Post Type Singular Name',
						'vz-secure-video'
				),
				'menu_name'             => __('Secure Videos', 'vz-secure-video'),
				'name_admin_bar'        => __('Secure Video', 'vz-secure-video'),
				'archives'              => __('Video Archives', 'vz-secure-video'),
				'attributes'            => __('Video Attributes', 'vz-secure-video'),
				'parent_item_colon'     => __('Parent Video:', 'vz-secure-video'),
				'all_items'             => __('All Videos', 'vz-secure-video'),
				'add_new_item'          => __('Add New Video', 'vz-secure-video'),
				'add_new'               => __('Add New', 'vz-secure-video'),
				'new_item'              => __('New Video', 'vz-secure-video'),
				'edit_item'             => __('Edit Video', 'vz-secure-video'),
				'update_item'           => __('Update Video', 'vz-secure-video'),
				'view_item'             => __('View Video', 'vz-secure-video'),
				'view_items'            => __('View Videos', 'vz-secure-video'),
				'search_items'          => __('Search Video', 'vz-secure-video'),
				'not_found'             => __('Not found', 'vz-secure-video'),
				'not_found_in_trash'    => __('Not found in Trash', 'vz-secure-video'),
				'featured_image'        => __('Video Thumbnail', 'vz-secure-video'),
				'set_featured_image'    => __(
						'Set video thumbnail',
						'vz-secure-video'
				),
				'remove_featured_image' => __(
						'Remove video thumbnail',
						'vz-secure-video'
				),
				'use_featured_image'    => __(
						'Use as video thumbnail',
						'vz-secure-video'
				),
				'insert_into_item'      => __(
						'Insert into video',
						'vz-secure-video'
				),
				'uploaded_to_this_item' => __(
						'Uploaded to this video',
						'vz-secure-video'
				),
				'items_list'            => __('Videos list', 'vz-secure-video'),
				'items_list_navigation' => __(
						'Videos list navigation',
						'vz-secure-video'
				),
				'filter_items_list'     => __(
						'Filter videos list',
						'vz-secure-video'
				),
		);

		$args = array(
				'label'                 => __('Secure Video', 'vz-secure-video'),
				'description'           => __(
						'Secure video streaming with access control',
						'vz-secure-video'
				),
				'labels'                => $labels,
				'supports'              => array('title', 'thumbnail', 'excerpt'),
				'taxonomies'            => array('category', 'post_tag'),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'menu_position'         => 5,
				'menu_icon'             => 'dashicons-video-alt3',
				'show_in_admin_bar'     => true,
				'show_in_nav_menus'     => true,
				'can_export'            => true,
				'has_archive'           => true,
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'capability_type'       => 'post',
				'show_in_rest'          => true,
		);

		register_post_type('vz_secure_video', $args);
}

/**
 * Register default WordPress taxonomies for Secure Video post type
 */
function vz_secure_video_register_taxonomies() {
		// Register Categories
		register_taxonomy_for_object_type('category', 'vz_secure_video');
		
		// Register Tags
		register_taxonomy_for_object_type('post_tag', 'vz_secure_video');
}

// Hook into WordPress
add_action('init', 'vz_secure_video_register_post_type');
add_action('init', 'vz_secure_video_register_taxonomies', 0);


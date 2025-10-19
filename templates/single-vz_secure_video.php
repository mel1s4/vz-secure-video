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

// Load the video player template
include VZ_SECURE_VIDEO_PLUGIN_DIR
    . 'vz-video-player.php';


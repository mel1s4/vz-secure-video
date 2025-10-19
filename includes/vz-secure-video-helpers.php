<?php
/**
 * Helper Functions
 * 
 * Utility functions for the plugin
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
		exit;
}

/**
 * Helper function to get the ZIP file URL for a video post
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return string|false The ZIP file URL or false if not set
 */
function vz_get_secure_video_zip_url($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		$zip_file_id = get_post_meta(
				$post_id,
				'_vz_secure_video_zip_file',
				true
		);
		
		if ($zip_file_id) {
				return wp_get_attachment_url($zip_file_id);
		}
		
		return false;
}

/**
 * Helper function to get the ZIP file ID for a video post
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return int|false The attachment ID or false if not set
 */
function vz_get_secure_video_zip_id($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		$zip_file_id = get_post_meta(
				$post_id,
				'_vz_secure_video_zip_file',
				true
		);
		
		return $zip_file_id ? intval($zip_file_id) : false;
}

/**
 * Get the extracted directory path for a video post
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return string|false The extracted directory path or false if not set
 */
function vz_get_secure_video_extracted_path($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		$extracted_path = get_post_meta(
				$post_id,
				'_vz_secure_video_extracted_path',
				true
		);
		
		return $extracted_path && file_exists($extracted_path)
				? $extracted_path
				: false;
}

/**
 * Get the M3U8 file path for a video post
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return string|false The M3U8 file path or false if not set
 */
function vz_get_secure_video_m3u8_path($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		$m3u8_path = get_post_meta(
				$post_id,
				'_vz_secure_video_m3u8_file',
				true
		);
		
		return $m3u8_path && file_exists($m3u8_path) ? $m3u8_path : false;
}

/**
 * Get the M3U8 file URL for a video post
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return string|false The M3U8 file URL or false if not set
 */
function vz_get_secure_video_m3u8_url($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		$m3u8_path = vz_get_secure_video_m3u8_path($post_id);
		
		if (!$m3u8_path) {
				return false;
		}
		
		$upload_dir = wp_upload_dir();
		$m3u8_url = str_replace(
				$upload_dir['basedir'],
				$upload_dir['baseurl'],
				$m3u8_path
		);
		
		return $m3u8_url;
}

/**
 * Get the video file ID for a video post
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return int|false The attachment ID or false if not set
 */
function vz_get_secure_video_video_id($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		$video_file_id = get_post_meta(
				$post_id,
				'_vz_secure_video_video_file',
				true
		);
		
		return $video_file_id ? intval($video_file_id) : false;
}

/**
 * Get the video file URL for a video post
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return string|false The video file URL or false if not set
 */
function vz_get_secure_video_video_url($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		$video_file_id = vz_get_secure_video_video_id($post_id);
		
		if (!$video_file_id) {
				return false;
		}
		
		return wp_get_attachment_url($video_file_id);
}

/**
 * Get the video file type (MIME type) for a video post
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return string|false The MIME type or false if not set
 */
function vz_get_secure_video_video_type($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		$video_file_id = vz_get_secure_video_video_id($post_id);
		
		if (!$video_file_id) {
				return false;
		}
		
		return get_post_mime_type($video_file_id);
}

/**
 * Check if video is HLS format
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return bool True if HLS, false otherwise
 */
function vz_is_secure_video_hls($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		// Check if M3U8 file exists
		$m3u8_path = vz_get_secure_video_m3u8_path($post_id);
		if ($m3u8_path) {
				return true;
		}
		
		// Check if video file is a ZIP (which would contain HLS)
		$video_type = vz_get_secure_video_video_type($post_id);
		if ($video_type === 'application/zip') {
				return true;
		}
		
		return false;
}

/**
 * Get the video source URL (either direct video or M3U8)
 * 
 * @param int $post_id The post ID (optional, defaults to current post)
 * @return array|false Array with 'url' and 'type' keys, or false if not set
 */
function vz_get_secure_video_source($post_id = null) {
		if (!$post_id) {
				$post_id = get_the_ID();
		}
		
		// First check for HLS M3U8 file
		$m3u8_url = vz_get_secure_video_m3u8_url($post_id);
		if ($m3u8_url) {
				return array(
						'url' => $m3u8_url,
						'type' => 'application/x-mpegURL'
				);
		}
		
		// Then check for direct video file
		$video_url = vz_get_secure_video_video_url($post_id);
		if ($video_url) {
				$video_type = vz_get_secure_video_video_type($post_id);
				return array(
						'url' => $video_url,
						'type' => $video_type
				);
		}
		
		// Finally check for legacy ZIP file
		$zip_url = vz_get_secure_video_zip_url($post_id);
		if ($zip_url) {
				return array(
						'url' => $zip_url,
						'type' => 'application/zip'
				);
		}
		
		return false;
}


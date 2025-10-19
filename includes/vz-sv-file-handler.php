<?php
/**
 * File Handler Functions
 * 
 * Handles ZIP extraction and file management
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
		exit;
}

/**
 * Extract ZIP file to secure directory
 * 
 * @param int $post_id The post ID
 * @param int $zip_file_id The attachment ID of the ZIP file
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function vz_secure_video_extract_zip($post_id, $zip_file_id) {
		// Check if ZipArchive class is available
		if (!class_exists('ZipArchive')) {
				return new WP_Error(
						'no_ziparchive',
						__('PHP ZipArchive extension is not available.', 'vz-secure-video')
				);
		}
		
		// Get the ZIP file path
		$zip_path = get_attached_file($zip_file_id);
		
		if (!$zip_path || !file_exists($zip_path)) {
				return new WP_Error(
						'file_not_found',
						__('ZIP file not found.', 'vz-secure-video')
				);
		}
		
		// Create the extraction directory
		$upload_dir = wp_upload_dir();
		$extract_dir = $upload_dir['basedir']
				. '/vz-secure-videos/'
				. $post_id;
		
		// Create directory if it doesn't exist
		if (!file_exists($extract_dir)) {
				wp_mkdir_p($extract_dir);
		}
		
		// Clean up old extracted files
		vz_secure_video_cleanup_extracted_files($post_id);
		
		// Open the ZIP file
		$zip = new ZipArchive();
		$result = $zip->open($zip_path);
		
		if ($result !== true) {
				return new WP_Error(
						'zip_open_failed',
						__('Failed to open ZIP file.', 'vz-secure-video')
				);
		}
		
		// Extract all files
		$extracted = $zip->extractTo($extract_dir);
		$zip->close();
		
		if (!$extracted) {
				return new WP_Error(
						'extraction_failed',
						__('Failed to extract ZIP file.', 'vz-secure-video')
				);
		}
		
		// Save the extraction path
		update_post_meta(
				$post_id,
				'_vz_secure_video_extracted_path',
				$extract_dir
		);
		
		// Get the M3U8 file (if exists)
		$m3u8_file = vz_secure_video_find_m3u8_file($extract_dir);
		if ($m3u8_file) {
				update_post_meta(
						$post_id,
						'_vz_secure_video_m3u8_file',
						$m3u8_file
				);
		}
		
		return true;
}

/**
 * Find the M3U8 file in extracted directory
 * 
 * @param string $directory Directory to search
 * @return string|false Path to M3U8 file or false if not found
 */
function vz_secure_video_find_m3u8_file($directory) {
		$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($directory),
				RecursiveIteratorIterator::LEAVES_ONLY
		);
		
		foreach ($iterator as $file) {
				if ($file->isFile() && $file->getExtension() === 'm3u8') {
						return $file->getPathname();
				}
		}
		
		return false;
}

/**
 * Clean up extracted files for a post
 * 
 * @param int $post_id The post ID
 * @return bool True on success
 */
function vz_secure_video_cleanup_extracted_files($post_id) {
		$extracted_path = get_post_meta(
				$post_id,
				'_vz_secure_video_extracted_path',
				true
		);
		
		if ($extracted_path && file_exists($extracted_path)) {
				// Delete the entire directory
				vz_secure_video_delete_directory($extracted_path);
				
				// Remove meta
				delete_post_meta($post_id, '_vz_secure_video_extracted_path');
				delete_post_meta($post_id, '_vz_secure_video_m3u8_file');
				
				return true;
		}
		
		return false;
}

/**
 * Recursively delete a directory
 * 
 * @param string $dir Directory to delete
 * @return bool True on success
 */
function vz_secure_video_delete_directory($dir) {
		if (!is_dir($dir)) {
				return false;
		}
		
		$files = array_diff(scandir($dir), array('.', '..'));
		
		foreach ($files as $file) {
				$path = $dir . '/' . $file;
				is_dir($path)
						? vz_secure_video_delete_directory($path)
						: unlink($path);
		}
		
		return rmdir($dir);
}

/**
 * Clean up extracted files when post is deleted
 * 
 * @param int $post_id The post ID
 */
function vz_secure_video_delete_post_cleanup($post_id) {
		$post_type = get_post_type($post_id);
		
		if ($post_type === 'vz_secure_video') {
				vz_secure_video_cleanup_extracted_files($post_id);
		}
}

// Hook into WordPress
add_action('before_delete_post', 'vz_secure_video_delete_post_cleanup');


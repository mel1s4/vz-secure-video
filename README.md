# Viroz Secure Video

A WordPress plugin that enables secure video streaming with granular access control and time-based permissions.

## Overview

Viroz Secure Video is a powerful plugin for managing and distributing video content with built-in security features. It allows you to upload videos, control who can access them, and set time-limited viewing permissions—perfect for paid courses, exclusive content, or private video libraries.

## Key Features

### Video Management
- **Custom Video Library**: Organize videos as a custom post type with full WordPress integration
- **Categories & Tags**: Reuse your existing WordPress categories and tags to organize video content
- **Multiple Video Formats**: Supports MP4, WebM, OGG, and HLS streaming formats
- **HLS Streaming**: Supports HTTP Live Streaming (HLS) format for adaptive video playback across devices
- **Easy Upload**: Upload video files directly or HLS ZIP archives through the WordPress media library
- **Automatic ZIP Extraction**: ZIP files are automatically extracted to `wp-content/uploads/vz-secure-videos/{post_id}/` when saved
- **M3U8 Auto-Detection**: Automatically finds and registers the M3U8 playlist file from extracted content

### Access Control & Permissions
- **User-Based Access**: Grant specific users permission to view individual videos
- **Time-Limited Access**: Set expiration dates for video access (e.g., 3 days, 30 days, etc.)
- **Permission Tracking**: Automatic tracking of when access was granted and when it expires
- **Admin Override**: Site administrators always have full access to all videos

### WooCommerce Integration
- **Virtual Products**: Link secure videos to WooCommerce virtual products
- **Automatic Access**: When a customer completes a purchase, they automatically receive video access
- **Flexible Duration**: Set different access durations for different products
- **Seamless Purchase Flow**: Access is granted immediately upon order completion

### Security Features
- **Protected Storage**: Videos are stored in a secure directory with restricted access
- **Access Verification**: Every video view is verified against the permissions database
- **Custom Redirects**: Configure where unauthorized users are redirected when access is denied
- **Permission Denied Messages**: Display custom messages for users without proper access

### User Experience
- **Modern Video Player**: Uses HLS.js for smooth, adaptive video playback
- **Responsive Design**: Videos play seamlessly on desktop, tablet, and mobile devices
- **Custom Templates**: Dedicated video viewing pages with a clean, focused interface
- **Automatic Playback**: Smart detection of browser HLS support for optimal compatibility

## How It Works

### For Content Creators
**Option 1: Direct Video Upload (Recommended for most users)**
1. Upload your video file (MP4, WebM, or OGG) through the WordPress media library
2. Create a "Secure Video" post and select your uploaded video file
3. Assign categories and tags to organize your video library (reuses your blog's categories/tags)
4. Optionally set a redirect URL for unauthorized access attempts

**Option 2: HLS Streaming (For advanced streaming)**
1. Convert your video to HLS format using FFmpeg
2. Compress the HLS files into a .zip archive
3. Upload the .zip file through the WordPress media library
4. Create a "Secure Video" post and select your uploaded ZIP file
5. **Save the post** - The ZIP file will be automatically extracted to `wp-content/uploads/vz-secure-videos/{post_id}/`
6. The plugin automatically detects and registers the M3U8 playlist file
7. Assign categories and tags to organize your video library (reuses your blog's categories/tags)
8. Optionally set a redirect URL for unauthorized access attempts

### For Manual Access Management
1. Edit any secure video post
2. Enter a username or email in the "User" field
3. Set the number of days the user should have access
4. Save the post—access is granted immediately

### For E-Commerce Integration
1. Create a virtual product in WooCommerce
2. Select a secure video from the dropdown menu
3. Set the access duration in days
4. When customers purchase the product, they automatically receive time-limited video access

## Use Cases

- **Online Courses**: Sell access to educational video content with time-limited viewing
- **Membership Sites**: Provide exclusive videos to members with automatic expiration
- **Private Training**: Share confidential training materials with specific users
- **Video Rentals**: Offer temporary access to premium video content
- **Corporate Training**: Distribute internal training videos with controlled access
- **Exclusive Content**: Create VIP content that's only accessible to certain users

## Technical Notes

- **Supported Video Formats**: MP4, WebM, OGG, and HLS (M3U8 playlists with segmented video files)
- **Recommended Format**: MP4 (H.264 codec) for best compatibility across all devices
- The plugin creates a dedicated secure folder in your uploads directory for HLS files
- Permissions are stored in a custom database table for efficient access control
- Compatible with WordPress 5.0+ and WooCommerce 3.0+

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start creating secure videos through the new "Secure Videos" menu item
4. (Optional) Install WooCommerce for e-commerce integration

### Recommended Plugins

For handling large ZIP file uploads (which can be several GB in size), we recommend installing one of these companion plugins:

- **Big File Uploads**: Uses chunked uploads to handle files of any size with a progress bar
- **WP Maximum Upload File Size**: Increases WordPress upload limits with a simple interface

You can install these recommended plugins directly from the WordPress admin:
1. Go to **Appearance > Recommended Plugins** (or **Plugins > Recommended Plugins**)
2. Click "Install" next to your preferred plugin
3. Activate the plugin

These plugins are **optional** but highly recommended if you're working with large video files.

## Converting Videos

### For Direct Video Upload (Recommended)
Simply upload your MP4, WebM, or OGG video files directly. No conversion needed!

### For HLS Streaming (Advanced)
If you want to use HLS streaming for adaptive bitrate playback, convert your videos to HLS format:

```bash
ffmpeg -i input.mp4 -hls_time 10 -hls_list_size 0 -f hls output.m3u8
```

Then compress the resulting files into a .zip archive for upload.

**Note**: HLS is optional and mainly beneficial for very large videos or when you need adaptive bitrate streaming. For most use cases, direct MP4 upload is simpler and works great.

## Developer Functions

### Get Videos by Category
```php
// Get all videos in a specific category
$videos = vz_get_secure_videos_by_category('tutorials');

if ($videos->have_posts()) {
    while ($videos->have_posts()) {
        $videos->the_post();
        // Display video
    }
    wp_reset_postdata();
}
```

### Get Videos by Tag
```php
// Get all videos with a specific tag
$videos = vz_get_secure_videos_by_tag('beginner');

if ($videos->have_posts()) {
    while ($videos->have_posts()) {
        $videos->the_post();
        // Display video
    }
    wp_reset_postdata();
}
```

### Get ZIP File URL
```php
// Get the ZIP file URL for a video
$zip_url = vz_get_secure_video_zip_url($post_id);

if ($zip_url) {
    echo '<a href="' . esc_url($zip_url) . '">Download Resources</a>';
}
```

### Get M3U8 File URL
```php
// Get the M3U8 file URL (automatically detected from extracted ZIP)
$m3u8_url = vz_get_secure_video_m3u8_url($post_id);

if ($m3u8_url) {
    echo '<video src="' . esc_url($m3u8_url) . '"></video>';
}
```

### Get Extracted Directory Path
```php
// Get the path to the extracted video files
$extracted_path = vz_get_secure_video_extracted_path($post_id);

if ($extracted_path) {
    echo 'Video files are located at: ' . esc_html($extracted_path);
}
```

### Get Video File URL
```php
// Get the direct video file URL (for MP4, WebM, OGG)
$video_url = vz_get_secure_video_video_url($post_id);

if ($video_url) {
    echo '<video src="' . esc_url($video_url) . '"></video>';
}
```

### Get Video Source
```php
// Get the video source (automatically detects HLS or direct video)
$video_source = vz_get_secure_video_source($post_id);

if ($video_source) {
    echo '<video src="' . esc_url($video_source['url']) . '" type="' . esc_attr($video_source['type']) . '"></video>';
}
```

### Check if Video is HLS
```php
// Check if the video is HLS format
$is_hls = vz_is_secure_video_hls($post_id);

if ($is_hls) {
    echo 'This video uses HLS streaming';
} else {
    echo 'This is a direct video file';
}
```

### Custom Query
```php
// Get videos with custom arguments
$videos = vz_get_secure_videos(array(
    'posts_per_page' => 10,
    'category_name' => 'courses',
    'tag' => 'premium',
    'orderby' => 'date',
    'order' => 'DESC'
));
```
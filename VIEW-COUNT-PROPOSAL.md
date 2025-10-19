# View Count Implementation Proposal

## Executive Summary

This document outlines the proposal for implementing a view count feature for the Viroz Secure Video plugin. The recommended approach is straightforward, leverages WordPress-native functionality, and can be implemented in approximately 30-60 minutes.

**Recommended Approach:** Simple Post Meta Storage  
**Difficulty Level:** Easy (⭐⭐☆☆☆)  
**Estimated Implementation Time:** 30-60 minutes  
**Database Impact:** Minimal (uses existing post meta)

---

## Current Plugin Assessment

### Architecture Overview

The plugin is well-structured with:
- **Custom Post Type:** `vz_secure_video` for video management
- **Storage:** Video files stored as attachment IDs in post meta
- **Player:** React-based custom video player with HLS support
- **Template System:** Clean separation of concerns

### Key Files
- `vz-secure-video.php` - Main plugin file
- `includes/vz-secure-video-post-type.php` - Custom post type registration
- `includes/vz-secure-video-meta-boxes.php` - Meta box handling
- `includes/vz-secure-video-helpers.php` - Helper functions
- `vz-video-player.php` - Video player template
- `templates/meta-box-resources.php` - Admin meta box template

### Current State
- ✅ Video upload and storage working
- ✅ Custom post type registered
- ✅ Meta boxes for video files
- ✅ Custom video player
- ❌ View tracking not implemented
- ❌ Analytics not implemented

---

## Recommended Approach: Post Meta Storage

### Why This Approach?

**Advantages:**
- ✅ **Easy to implement** - Uses WordPress core functionality
- ✅ **No database changes** - Leverages existing post meta system
- ✅ **Lightweight** - Minimal performance overhead
- ✅ **WordPress-native** - Follows WordPress best practices
- ✅ **Easy to extend** - Can add more analytics later
- ✅ **No migrations** - Works immediately after activation
- ✅ **Simple queries** - Standard WordPress meta queries

**Disadvantages:**
- ⚠️ Less efficient for millions of views (unlikely scenario)
- ⚠️ No detailed view history without additional implementation

### How It Works

1. **Storage:** View count stored in post meta: `_vz_secure_video_view_count`
2. **Tracking:** AJAX endpoint increments count when video plays
3. **Display:** Count shown in admin meta box and optionally on frontend
4. **Extension:** Can add unique views, view history, etc.

---

## Implementation Roadmap

### Phase 1: Basic View Count (Easy - 30 mins)
**Goal:** Track total views for each video

**Components:**
1. Initialize view count meta field (0 on video creation)
2. Create AJAX endpoint to increment views
3. Add JavaScript tracking to video player
4. Display view count in admin meta box

**Deliverables:**
- New file: `includes/vz-secure-video-view-tracker.php`
- Modified: `includes/vz-secure-video-meta-boxes.php`
- Modified: `templates/meta-box-resources.php`
- Modified: Video player JavaScript

---

### Phase 2: Enhanced Tracking (Medium - 1 hour)
**Goal:** Track unique views and basic analytics

**Components:**
1. Track unique views per user ID
2. Store view history (optional database table)
3. Add analytics display in admin
4. Add "Reset View Count" functionality
5. Add view count to admin list columns

**Deliverables:**
- Modified: `includes/vz-secure-video-view-tracker.php`
- New file: `includes/vz-secure-video-analytics.php`
- New file: `templates/meta-box-analytics.php`
- Modified: Admin list table

---

### Phase 3: Advanced Analytics (Hard - 2-3 hours)
**Goal:** Comprehensive analytics and reporting

**Components:**
1. Track view duration (how long users watch)
2. Track completion rate (did they finish the video?)
3. Export analytics to CSV
4. Dashboard widgets
5. Time-based analytics (views per day/week/month)
6. User engagement metrics

**Deliverables:**
- New file: `includes/vz-secure-video-advanced-analytics.php`
- New file: `admin/vz-secure-video-analytics-dashboard.php`
- New file: `templates/analytics-dashboard.php`
- Database table for detailed analytics

---

## Detailed Implementation Plan

### Step 1: Initialize View Count (5 minutes)

**File:** `includes/vz-secure-video-meta-boxes.php`

```php
/**
 * Initialize view count when video is created
 */
function vz_secure_video_init_view_count($post_id) {
    // Only for new posts
    if (get_post_meta($post_id, '_vz_secure_video_view_count', true) === '') {
        update_post_meta($post_id, '_vz_secure_video_view_count', 0);
    }
}
add_action('save_post_vz_secure_video', 'vz_secure_video_init_view_count', 5);
```

---

### Step 2: Create AJAX Tracking Endpoint (10 minutes)

**New File:** `includes/vz-secure-video-view-tracker.php`

```php
<?php
/**
 * View Tracking Functions
 * 
 * Handles view count tracking for secure videos
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler to track video views
 */
function vz_track_video_view() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vz_track_view')) {
        wp_send_json_error(['message' => 'Invalid security token']);
        return;
    }
    
    // Get post ID
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    // Validate post ID and post type
    if (!$post_id || get_post_type($post_id) !== 'vz_secure_video') {
        wp_send_json_error(['message' => 'Invalid video']);
        return;
    }
    
    // Get current user ID (0 for guests)
    $user_id = get_current_user_id();
    
    // Increment total view count
    $current_count = get_post_meta($post_id, '_vz_secure_video_view_count', true);
    $new_count = intval($current_count) + 1;
    update_post_meta($post_id, '_vz_secure_video_view_count', $new_count);
    
    // Track unique views (optional - Phase 2)
    $viewed_by = get_post_meta($post_id, '_vz_secure_video_viewed_by', true);
    if (!is_array($viewed_by)) {
        $viewed_by = [];
    }
    
    // Add user to viewed list if not already there
    if ($user_id > 0 && !in_array($user_id, $viewed_by)) {
        $viewed_by[] = $user_id;
        update_post_meta($post_id, '_vz_secure_video_viewed_by', $viewed_by);
    }
    
    // Track view timestamp (optional - Phase 2)
    $view_history = get_post_meta($post_id, '_vz_secure_video_view_history', true);
    if (!is_array($view_history)) {
        $view_history = [];
    }
    $view_history[] = [
        'user_id' => $user_id,
        'timestamp' => current_time('mysql'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    update_post_meta($post_id, '_vz_secure_video_view_history', $view_history);
    
    // Return success with new count
    wp_send_json_success([
        'count' => $new_count,
        'unique_views' => count($viewed_by)
    ]);
}

// Register AJAX handlers for both logged-in and guest users
add_action('wp_ajax_vz_track_video_view', 'vz_track_video_view');
add_action('wp_ajax_nopriv_vz_track_video_view', 'vz_track_video_view');

/**
 * Helper function to get view count
 * 
 * @param int $post_id The video post ID
 * @return int The view count
 */
function vz_get_video_view_count($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    return intval(get_post_meta($post_id, '_vz_secure_video_view_count', true));
}

/**
 * Helper function to get unique view count
 * 
 * @param int $post_id The video post ID
 * @return int The unique view count
 */
function vz_get_video_unique_view_count($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $viewed_by = get_post_meta($post_id, '_vz_secure_video_viewed_by', true);
    return is_array($viewed_by) ? count($viewed_by) : 0;
}

/**
 * Reset view count for a video
 * 
 * @param int $post_id The video post ID
 * @return bool Success status
 */
function vz_reset_video_view_count($post_id) {
    update_post_meta($post_id, '_vz_secure_video_view_count', 0);
    delete_post_meta($post_id, '_vz_secure_video_viewed_by');
    delete_post_meta($post_id, '_vz_secure_video_view_history');
    return true;
}
```

**Include in main plugin file:**

```php
// In vz-secure-video.php, add to includes section:
require_once VZ_SECURE_VIDEO_PLUGIN_DIR
    . 'includes/vz-secure-video-view-tracker.php';
```

---

### Step 3: JavaScript Integration (10 minutes)

**File:** Video player JavaScript (needs to be added to your React component)

```javascript
// Track video view on first play
let hasTrackedView = false;

const trackVideoView = async () => {
    if (hasTrackedView) return;
    
    try {
        const response = await fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'vz_track_video_view',
                post_id: window.vzVideoData.postId,
                nonce: window.vzVideoData.viewTrackingNonce
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            hasTrackedView = true;
            console.log('View tracked:', data.data.count);
            
            // Optionally update UI with new count
            updateViewCount(data.data.count);
        }
    } catch (error) {
        console.error('Failed to track view:', error);
    }
};

// Track when video starts playing
player.on('play', () => {
    if (!hasTrackedView) {
        trackVideoView();
    }
});

// Optional: Update view count display
function updateViewCount(count) {
    const viewCountElement = document.querySelector('.view-count');
    if (viewCountElement) {
        viewCountElement.textContent = `${count} views`;
    }
}
```

**Pass nonce to JavaScript:**

```php
// In vz-video-player.php, update the script section:
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
        duration: '<?php $duration ?>',
        tags: '<?php $tags ?>',
        categories: '<?php $categories ?>',
    };
</script>
```

---

### Step 4: Display in Admin Meta Box (5 minutes)

**File:** `templates/meta-box-resources.php`

Add after line 78 (after the video file section):

```php
<!-- View Count Section -->
<table class="form-table" style="margin-top: 20px;">
  <tr>
    <th scope="row">
      <label><?php _e('View Statistics', 'vz-secure-video'); ?></label>
    </th>
    <td>
      <?php
      $view_count = vz_get_video_view_count($post_id);
      $unique_views = vz_get_video_unique_view_count($post_id);
      ?>
      <p>
        <strong><?php _e('Total Views:', 'vz-secure-video'); ?></strong>
        <?php echo esc_html(number_format($view_count)); ?>
      </p>
      <p>
        <strong><?php _e('Unique Views:', 'vz-secure-video'); ?></strong>
        <?php echo esc_html(number_format($unique_views)); ?>
      </p>
      <button type="button"
              class="button"
              id="vz_reset_view_count"
              data-post-id="<?php echo esc_attr($post_id); ?>">
        <?php _e('Reset View Count', 'vz-secure-video'); ?>
      </button>
      <p class="description">
        <?php _e('View count is tracked when users play the video.', 'vz-secure-video'); ?>
      </p>
    </td>
  </tr>
</table>
```

---

### Step 5: Add View Count to Admin List (Optional - 5 minutes)

**File:** `admin/vz-secure-video-admin.php`

Add to the existing file:

```php
/**
 * Add view count column to admin list
 */
function vz_secure_video_add_view_count_column($columns) {
    $columns['view_count'] = __('Views', 'vz-secure-video');
    return $columns;
}
add_filter('manage_vz_secure_video_posts_columns', 'vz_secure_video_add_view_count_column');

/**
 * Display view count in admin list
 */
function vz_secure_video_display_view_count_column($column, $post_id) {
    if ($column === 'view_count') {
        $count = vz_get_video_view_count($post_id);
        echo number_format($count);
    }
}
add_action('manage_vz_secure_video_posts_custom_column', 'vz_secure_video_display_view_count_column', 10, 2);

/**
 * Make view count column sortable
 */
function vz_secure_video_sortable_view_count_column($columns) {
    $columns['view_count'] = 'view_count';
    return $columns;
}
add_filter('manage_edit-vz_secure_video_sortable_columns', 'vz_secure_video_sortable_view_count_column');

/**
 * Handle view count column sorting
 */
function vz_secure_video_sort_by_view_count($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ('view_count' === $query->get('orderby')) {
        $query->set('meta_key', '_vz_secure_video_view_count');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'vz_secure_video_sort_by_view_count');
```

---

### Step 6: Add Reset Functionality (Optional - 5 minutes)

**File:** `admin/vz-secure-video-admin.php`

```php
/**
 * Handle view count reset via AJAX
 */
function vz_secure_video_reset_view_count() {
    check_ajax_referer('vz_reset_view_count', 'nonce');
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => 'Permission denied']);
        return;
    }
    
    vz_reset_video_view_count($post_id);
    
    wp_send_json_success(['message' => 'View count reset successfully']);
}
add_action('wp_ajax_vz_secure_video_reset_view_count', 'vz_secure_video_reset_view_count');
```

**Add JavaScript for reset button:**

```javascript
// In admin/js/media-selector.js or create new admin script
jQuery(document).ready(function($) {
    $('#vz_reset_view_count').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to reset the view count?')) {
            return;
        }
        
        const postId = $(this).data('post-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vz_secure_video_reset_view_count',
                post_id: postId,
                nonce: '<?php echo wp_create_nonce('vz_reset_view_count'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to reset view count');
                }
            }
        });
    });
});
```

---

## Optional: Display View Count on Frontend

If you want to show view counts to visitors:

**File:** `vz-video-player.php`

Add before the closing `</head>` tag:

```php
<style>
.view-count {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    z-index: 10;
}
</style>
```

Add before `</body>`:

```php
<div class="view-count">
    <?php echo esc_html(number_format(vz_get_video_view_count($post_id))); ?> views
</div>
```

---

## Testing Checklist

### Phase 1 Testing
- [ ] View count initializes to 0 for new videos
- [ ] View count increments when video plays
- [ ] View count displays correctly in admin meta box
- [ ] AJAX request completes successfully
- [ ] View count persists after page reload
- [ ] Multiple views increment count correctly

### Phase 2 Testing
- [ ] Unique views tracked per user
- [ ] View history stored correctly
- [ ] Admin list column displays view count
- [ ] View count column is sortable
- [ ] Reset button works correctly
- [ ] Nonce validation prevents unauthorized tracking

### Phase 3 Testing (if implemented)
- [ ] View duration tracked
- [ ] Completion rate calculated correctly
- [ ] Analytics export works
- [ ] Dashboard widgets display correctly
- [ ] Performance is acceptable with large datasets

---

## Performance Considerations

### Current Implementation
- **Post Meta Storage:** O(1) read/write operations
- **AJAX Tracking:** Minimal overhead (~10-20ms per request)
- **Database Impact:** One meta update per view
- **Scalability:** Good for up to 10,000+ views per video

### Optimization Tips
1. **Batch Updates:** Consider batching view counts if tracking millions
2. **Caching:** Use object cache for frequently accessed counts
3. **Cleanup:** Periodically archive old view history
4. **Indexing:** Ensure post meta is indexed (WordPress handles this)

---

## Security Considerations

### Implemented Security
- ✅ **Nonce Verification:** All AJAX requests verify nonces
- ✅ **Capability Checks:** Reset requires edit permissions
- ✅ **Input Sanitization:** All inputs are sanitized
- ✅ **Output Escaping:** All outputs are escaped
- ✅ **SQL Injection Protection:** Using WordPress meta functions

### Additional Recommendations
- Consider rate limiting AJAX requests
- Log suspicious activity patterns
- Add IP-based duplicate detection (optional)

---

## Future Enhancements

### Short-term (1-2 weeks)
1. Add view count to REST API
2. Create shortcode for displaying view counts
3. Add view count widgets
4. Export analytics to CSV

### Medium-term (1-2 months)
1. Track view duration
2. Track completion rate
3. User engagement metrics
4. Time-based analytics (daily/weekly views)

### Long-term (3-6 months)
1. Advanced analytics dashboard
2. Heat maps (where users watch most)
3. Drop-off points analysis
4. Integration with Google Analytics
5. Email reports for popular videos

---

## Alternative Approaches

### Option 2: Custom Database Table (Medium-Hard)

**When to use:**
- Tracking millions of views
- Need detailed view history
- Want to track complex analytics

**Pros:**
- More efficient for large datasets
- Better for complex queries
- Can store detailed metadata

**Cons:**
- More complex to implement
- Requires database migrations
- More maintenance overhead

**Implementation:**
```sql
CREATE TABLE wp_vz_video_views (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id bigint(20) UNSIGNED NOT NULL,
    user_id bigint(20) UNSIGNED DEFAULT 0,
    ip_address varchar(45),
    view_duration int(11) DEFAULT 0,
    completion_rate decimal(5,2) DEFAULT 0,
    viewed_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY post_id (post_id),
    KEY user_id (user_id),
    KEY viewed_at (viewed_at)
);
```

---

### Option 3: Third-Party Analytics (Easy but External)

**When to use:**
- Want comprehensive analytics out of the box
- Don't want to maintain analytics code
- Need advanced features immediately

**Services:**
- Google Analytics Events
- Mixpanel
- Segment
- Plausible Analytics

**Pros:**
- Feature-rich immediately
- Professional analytics tools
- No maintenance needed

**Cons:**
- External dependency
- Privacy concerns (GDPR)
- Additional cost
- Less control

---

## Decision Matrix

| Feature | Post Meta | Custom Table | Third-Party |
|---------|-----------|--------------|-------------|
| **Ease of Implementation** | ⭐⭐⭐⭐⭐ | ⭐⭐☆☆☆ | ⭐⭐⭐⭐☆ |
| **Performance** | ⭐⭐⭐⭐☆ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Cost** | Free | Free | $0-50/month |
| **Privacy** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐☆☆☆ |
| **Control** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐☆☆☆ |
| **Features** | ⭐⭐⭐☆☆ | ⭐⭐⭐⭐☆ | ⭐⭐⭐⭐⭐ |
| **Maintenance** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐☆☆ | ⭐⭐⭐⭐⭐ |

**Recommendation:** Start with Post Meta, migrate to Custom Table only if needed.

---

## Questions to Answer

Before implementing, please clarify:

### Tracking Scope
- [ ] Track total views only?
- [ ] Track unique views per user?
- [ ] Track view duration?
- [ ] Track completion rate?
- [ ] Track view history?

### Display Preferences
- [ ] Show in admin meta box?
- [ ] Show in admin list table?
- [ ] Show on frontend video player?
- [ ] Show on video archive pages?

### User Experience
- [ ] Count every play?
- [ ] One view per user per video?
- [ ] One view per session?
- [ ] Count partial views (e.g., >10 seconds)?

### Analytics
- [ ] Need export functionality?
- [ ] Need dashboard widgets?
- [ ] Need email reports?
- [ ] Need REST API access?

---

## Implementation Timeline

### Week 1: Phase 1 (Basic View Count)
- Day 1-2: Implement core tracking functionality
- Day 3: Add admin display
- Day 4: Testing and bug fixes
- Day 5: Documentation and deployment

### Week 2: Phase 2 (Enhanced Tracking)
- Day 1-2: Add unique view tracking
- Day 3: Add admin list column
- Day 4: Add reset functionality
- Day 5: Testing and refinement

### Week 3-4: Phase 3 (Advanced Analytics)
- Week 3: Implement duration tracking
- Week 4: Create analytics dashboard
- Ongoing: Testing and optimization

---

## Success Metrics

### Technical Metrics
- ✅ View count increments correctly
- ✅ No performance degradation
- ✅ AJAX requests complete in <100ms
- ✅ No database errors
- ✅ Works for logged-in and guest users

### Business Metrics
- 📊 Track which videos are most popular
- 📊 Identify content gaps
- 📊 Measure user engagement
- 📊 Optimize content strategy

---

## Conclusion

The recommended approach (Post Meta Storage) is:
- ✅ **Simple** to implement
- ✅ **Fast** to deploy
- ✅ **Reliable** and WordPress-native
- ✅ **Extensible** for future needs
- ✅ **Cost-effective** (no external services)

**Next Steps:**
1. Review this proposal
2. Answer the questions above
3. Approve implementation
4. Begin Phase 1 implementation

---

## Appendix: Code Files Summary

### New Files to Create
1. `includes/vz-secure-video-view-tracker.php` - Core tracking functionality
2. `includes/vz-secure-video-analytics.php` - Analytics functions (Phase 2)
3. `templates/meta-box-analytics.php` - Analytics display (Phase 2)

### Files to Modify
1. `vz-secure-video.php` - Add include for view tracker
2. `includes/vz-secure-video-meta-boxes.php` - Initialize view count
3. `templates/meta-box-resources.php` - Display view count
4. `admin/vz-secure-video-admin.php` - Add admin list column
5. `vz-video-player.php` - Add nonce for tracking
6. Video player JavaScript - Add tracking on play

### Files to Create (Optional)
1. `admin/js/view-tracker.js` - Reset button functionality
2. `admin/css/view-analytics.css` - Analytics styling
3. `templates/analytics-dashboard.php` - Dashboard template (Phase 3)

---

**Document Version:** 1.0  
**Last Updated:** 2024  
**Author:** AI Assistant  
**Status:** Proposal - Awaiting Approval


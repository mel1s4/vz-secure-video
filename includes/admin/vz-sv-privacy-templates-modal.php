<?php
/**
 * Privacy Policy Templates Modal
 * 
 * Modal template for displaying privacy policy templates
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}
?>

<!-- Privacy Policy Templates Modal -->
<div id="vz-privacy-templates-modal" class="vz-modal" style="display: none;">
  <div class="vz-modal-content">
    <div class="vz-modal-header">
      <h2><?php _e('Privacy Policy Templates', 'vz-secure-video'); ?></h2>
      <button type="button" class="vz-modal-close">&times;</button>
    </div>
    <div class="vz-modal-body">
      <p class="vz-modal-intro">
        <?php _e(
          'Use these templates to help you create a privacy policy for ' .
          'your website. Customize the placeholders and integrate into ' .
          'your existing privacy policy.',
          'vz-secure-video'
        ); ?>
      </p>
      
      <div class="vz-tabs">
        <button class="vz-tab-button active" data-tab="brief">
          <?php _e('Brief Version', 'vz-secure-video'); ?>
        </button>
        <button class="vz-tab-button" data-tab="detailed">
          <?php _e('Detailed Version', 'vz-secure-video'); ?>
        </button>
      </div>
      
      <div id="vz-tab-brief" class="vz-tab-content active">
        <h3>
          <?php _e(
            'Brief Version (Recommended for most sites)',
            'vz-secure-video'
          ); ?>
        </h3>
        <div class="vz-template-box">
          <pre id="vz-template-brief"><?php
            echo esc_html(
              'Viroz Secure Video Plugin

This site uses the Viroz Secure Video plugin to manage access to ' .
              'video content. 
The plugin collects the following data:

• User identification (user ID, email address, username) for ' .
              'access control
• IP address for security and analytics
• Device and browser information for compatibility
• Video viewing history and duration for analytics
• Access permissions and expiry dates for access management

Data is retained for [X days - configure in plugin settings] and ' .
              'is used to:
• Control access to secure video content
• Enforce time-limited viewing permissions
• Track video performance and user engagement
• Prevent unauthorized access

You can request a copy of your data, request deletion, or opt-out ' .
              'of analytics 
tracking at any time by contacting [your email address].'
            );
          ?></pre>
          <button 
            type="button" 
            class="button button-primary vz-copy-button" 
            data-copy="vz-template-brief"
          >
            <span 
              class="dashicons dashicons-clipboard" 
              style="vertical-align: middle; margin-top: 3px;"
            ></span>
            <?php _e('Copy to Clipboard', 'vz-secure-video'); ?>
          </button>
        </div>
      </div>
      
      <div id="vz-tab-detailed" class="vz-tab-content">
        <h3>
          <?php _e(
            'Detailed Version (Recommended for GDPR compliance)',
            'vz-secure-video'
          ); ?>
        </h3>
        <div class="vz-template-box">
          <pre id="vz-template-detailed"><?php
            echo esc_html(
              'Viroz Secure Video Plugin Data Collection

This website uses the Viroz Secure Video plugin to manage and ' .
              'distribute video 
content with access control and time-limited viewing permissions.

Personal Data Collected

The plugin collects the following personal data:

1. User Identification Data
   • User ID (WordPress user ID)
   • Email address (if applicable)
   • Username
   
   Purpose: Access control and permission management
   Legal Basis: Contract (necessary for service delivery)
   Retention: [X days]

2. IP Address
   • IP address when viewing videos
   
   Purpose: Security, fraud prevention, and analytics
   Legal Basis: Legitimate interest
   Retention: [X days]
   Note: IP addresses can be anonymized in plugin settings

3. Device and Browser Information
   • User agent (browser type, version, device)
   • Viewing device type
   
   Purpose: Compatibility and technical support
   Legal Basis: Legitimate interest
   Retention: [X days]

4. Video Viewing Data
   • Which videos were viewed
   • View duration and completion status
   • Access timestamps
   
   Purpose: Analytics, access control, and business intelligence
   Legal Basis: Legitimate interest
   Retention: [X days]

5. Permission Management Data
   • Access permissions for each video
   • Access duration and expiry dates
   • Permission grant dates
   
   Purpose: Service delivery and access control
   Legal Basis: Contract (necessary for service delivery)
   Retention: [X days]

How Data is Used

Your data is used to:
• Verify access permissions for secure video content
• Enforce time-limited viewing permissions
• Track video performance and user engagement
• Prevent unauthorized access and detect suspicious activity
• Provide technical support and diagnose playback issues
• Maintain audit trails for compliance purposes

Data Sharing

Your data is stored on our hosting server and is not shared with ' .
              'third parties 
by default. However, if we use a Content Delivery Network (CDN) ' .
              'or analytics 
services, your data may be processed by those services in ' .
              'accordance with their 
privacy policies.

Your Rights

Under GDPR, you have the right to:
• Access your personal data
• Rectify inaccurate data
• Request deletion of your data ("right to be forgotten")
• Receive your data in a portable format
• Object to processing of your data
• Restrict how we use your data
• Withdraw consent at any time

To exercise these rights, please contact us at [your email ' .
              'address] or use the 
plugin\'s built-in data export and deletion features.

Data Retention

Your data is retained for [X days] and is automatically deleted ' .
              'after this period. 
You can request immediate deletion of your data at any time.

Contact Information

If you have questions about how we handle your data, please ' .
              'contact:
• Email: [your email address]
• Address: [your physical address]
• Data Protection Officer: [if applicable]'
            );
          ?></pre>
          <button 
            type="button" 
            class="button button-primary vz-copy-button" 
            data-copy="vz-template-detailed"
          >
            <span 
              class="dashicons dashicons-clipboard" 
              style="vertical-align: middle; margin-top: 3px;"
            ></span>
            <?php _e('Copy to Clipboard', 'vz-secure-video'); ?>
          </button>
        </div>
      </div>
      
      <div class="vz-modal-footer">
        <p>
          <strong><?php _e('Important:', 'vz-secure-video'); ?></strong> 
          <?php _e(
            'Remember to replace the placeholders [X days] and ' .
            '[your email address] with your actual information before ' .
            'publishing.',
            'vz-secure-video'
          ); ?>
        </p>
        <p>
          <a 
            href="<?php 
              echo esc_url(
                plugin_dir_url(dirname(dirname(__FILE__))) . 
                'docs/PRIVACY-POLICY-GUIDANCE.md'
              ); 
            ?>" 
            target="_blank" 
            class="button button-secondary"
          >
            <span 
              class="dashicons dashicons-book-alt" 
              style="vertical-align: middle; margin-top: 3px;"
            ></span>
            <?php _e('View Full Documentation', 'vz-secure-video'); ?>
          </a>
        </p>
      </div>
    </div>
  </div>
</div>


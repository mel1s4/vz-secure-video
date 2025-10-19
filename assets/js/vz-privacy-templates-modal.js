/**
 * Privacy Policy Templates Modal JavaScript
 * 
 * Handles modal interactions for privacy policy templates
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

(function($) {
  'use strict';

  $(document).ready(function() {
    // Open modal
    $('#vz-show-privacy-templates').on('click', function() {
      $('#vz-privacy-templates-modal').fadeIn(200);
    });

    // Close modal
    $('.vz-modal-close, #vz-privacy-templates-modal').on(
      'click',
      function(e) {
        if (e.target === this) {
          $('#vz-privacy-templates-modal').fadeOut(200);
        }
      }
    );

    // Tab switching
    $('.vz-tab-button').on('click', function() {
      var tabId = $(this).data('tab');

      // Update buttons
      $('.vz-tab-button').removeClass('active');
      $(this).addClass('active');

      // Update content
      $('.vz-tab-content').removeClass('active');
      $('#vz-tab-' + tabId).addClass('active');
    });

    // Copy to clipboard
    $('.vz-copy-button').on('click', function() {
      var button = $(this);
      var targetId = button.data('copy');
      var text = $('#' + targetId).text();

      // Create temporary textarea
      var tempTextarea = $('<textarea>');
      $('body').append(tempTextarea);
      tempTextarea.val(text).select();

      try {
        var successful = document.execCommand('copy');
        if (successful) {
          // Show success feedback
          var originalHtml = button.html();
          button.addClass('copied').html(
            '<span class="dashicons dashicons-yes" ' +
            'style="vertical-align: middle; margin-top: 3px;"></span> ' +
            vzPrivacyTemplates.copiedText
          );

          setTimeout(function() {
            button.removeClass('copied').html(originalHtml);
          }, 2000);
        } else {
          alert(vzPrivacyTemplates.copyFailedText);
        }
      } catch (err) {
        alert(vzPrivacyTemplates.copyFailedText);
      }

      tempTextarea.remove();
    });

    // Close on Escape key
    $(document).on('keydown', function(e) {
      if (
        e.key === 'Escape' &&
        $('#vz-privacy-templates-modal').is(':visible')
      ) {
        $('#vz-privacy-templates-modal').fadeOut(200);
      }
    });
  });
})(jQuery);


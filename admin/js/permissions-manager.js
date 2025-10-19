/**
 * Permissions Manager JavaScript
 * 
 * Handles AJAX operations for video permissions
 *
 * @package VirozSecureVideo
 * @since 1.0.0
 */

(function($) {
		'use strict';

		/**
		 * Initialize permissions manager
		 */
		function initPermissionsManager() {
				const postId = vzPermissionsData.postId;
				const grantNonce = vzPermissionsData.grantNonce;
				const revokeNonce = vzPermissionsData.revokeNonce;

				// Add permission via AJAX
				$('#vz_add_permission').on('click', function() {
						const userId = $('#vz_permission_user').val();
						const viewLimit = $('#vz_permission_view_limit').val();
						const expiresAt = $('#vz_permission_expires_at').val();

						if (!userId) {
								alert(vzPermissionsData.messages.selectUser);
								return;
						}

						$.ajax({
								url: ajaxurl,
								type: 'POST',
								data: {
										action: 'vz_grant_permission',
										post_id: postId,
										user_id: userId,
										view_limit: viewLimit || null,
										expires_at: expiresAt || null,
										nonce: grantNonce
								},
								success: function(response) {
										if (response.success) {
												location.reload();
										} else {
												alert(response.data.message || vzPermissionsData.messages.grantFailed);
										}
								},
								error: function() {
										alert(vzPermissionsData.messages.ajaxError);
								}
						});
				});

				// Revoke permission via AJAX
				$(document).on('click', '.vz-revoke-permission', function() {
						if (!confirm(vzPermissionsData.messages.revokeConfirm)) {
								return;
						}

						const button = $(this);
						const postId = button.data('post-id');
						const userId = button.data('user-id');

						$.ajax({
								url: ajaxurl,
								type: 'POST',
								data: {
										action: 'vz_revoke_permission',
										post_id: postId,
										user_id: userId,
										nonce: revokeNonce
								},
								success: function(response) {
										if (response.success) {
												location.reload();
										} else {
												alert(response.data.message || vzPermissionsData.messages.revokeFailed);
										}
								},
								error: function() {
										alert(vzPermissionsData.messages.ajaxError);
								}
						});
				});
		}

		// Initialize when document is ready
		$(document).ready(function() {
				if (typeof vzPermissionsData !== 'undefined') {
						initPermissionsManager();
				}
		});

})(jQuery);


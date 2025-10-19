jQuery(document).ready(function($) {
	var file_frame;
	
	// Video file elements
	var videoFileInput = $('#vz_secure_video_video_file');
	var videoFilePreview = $('#vz_video_file_preview');
	var selectVideoButton = $('#vz_select_video_file');
	var removeVideoButton = $('#vz_remove_video_file');
	
	// ZIP file elements
	var zipFileInput = $('#vz_secure_video_zip_file');
	var zipFilePreview = $('#vz_zip_file_preview');
	var selectZipButton = $('#vz_select_zip_file');
	var removeZipButton = $('#vz_remove_zip_file');

	// When the select video button is clicked
	selectVideoButton.on('click', function(e) {
		e.preventDefault();

		// If the media frame already exists, reopen it
		if (file_frame) {
			file_frame.open();
			return;
		}

		// Create the media frame for video files
		file_frame = wp.media({
			title: vzSecureVideo.videoTitle,
			button: {
				text: vzSecureVideo.button
			},
			library: {
				type: ['video/mp4', 'video/webm', 'video/ogg', 'application/zip']
			},
			multiple: false
		});

		// When a file is selected, run a callback
		file_frame.on('select', function() {
			var attachment = file_frame.state().get('selection').first().toJSON();
			
			// Set the hidden input value
			videoFileInput.val(attachment.id);
			
			// Display the selected file
			var previewHtml = '<p><strong>' + vzSecureVideo.selectedLabel + '</strong> <a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a></p>';
			previewHtml += '<button type="button" class="button" id="vz_remove_video_file">' + vzSecureVideo.removeLabel + '</button>';
			videoFilePreview.html(previewHtml);
			
			// Re-bind the remove button event
			$('#vz_remove_video_file').on('click', removeVideoFile);
			
			// Clear the file_frame for next use
			file_frame = null;
		});

		// Open the modal
		file_frame.open();
	});

	// When the select ZIP button is clicked
	selectZipButton.on('click', function(e) {
		e.preventDefault();

		// If the media frame already exists, reopen it
		if (file_frame) {
			file_frame.open();
			return;
		}

		// Create the media frame for ZIP files
		file_frame = wp.media({
			title: vzSecureVideo.zipTitle,
			button: {
				text: vzSecureVideo.button
			},
			library: {
				type: 'application/zip'
			},
			multiple: false
		});

		// When a file is selected, run a callback
		file_frame.on('select', function() {
			var attachment = file_frame.state().get('selection').first().toJSON();
			
			// Set the hidden input value
			zipFileInput.val(attachment.id);
			
			// Display the selected file
			var previewHtml = '<p><strong>' + vzSecureVideo.selectedLabel + '</strong> <a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a></p>';
			previewHtml += '<button type="button" class="button" id="vz_remove_zip_file">' + vzSecureVideo.removeLabel + '</button>';
			zipFilePreview.html(previewHtml);
			
			// Re-bind the remove button event
			$('#vz_remove_zip_file').on('click', removeZipFile);
			
			// Clear the file_frame for next use
			file_frame = null;
		});

		// Open the modal
		file_frame.open();
	});

	// Remove video file button handler
	function removeVideoFile() {
		videoFileInput.val('');
		videoFilePreview.html('');
	}

	// Remove ZIP file button handler
	function removeZipFile() {
		zipFileInput.val('');
		zipFilePreview.html('');
	}

	// Bind remove button events if they exist on page load
	if (removeVideoButton.length) {
		removeVideoButton.on('click', removeVideoFile);
	}
	
	if (removeZipButton.length) {
		removeZipButton.on('click', removeZipFile);
	}
});


(function($) { 
  $(document).ready(function() {
    var file_frame;
    $('#vz_secure_video_file_button').on('click', function(event) {
      event.preventDefault();
      if (file_frame) {
        file_frame.open();
        return;
      }
      file_frame = wp.media({
        title: 'Choose a Video',
        button: {
          text: 'Use this video'
        },
        library: {
          // type: 'video', type is a zip file
          type: 'application/zip',
        },
        multiple: false
      });
      file_frame.on('select', function() {
        var attachment = file_frame.state().get('selection').first().toJSON();
        const videoId = attachment.id;
        const videoInput = document.getElementById('vz_secure_video_file_input');
        const videoName = document.getElementById('vz_secure_video_file_name');
        videoName.value = attachment.name;
        videoInput.value = videoId;
        console.log(videoInput);
      });
      file_frame.open();
    });
  });
}
)(jQuery);
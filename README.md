

# Convert your video to HSL
ffmpeg -i pelicula.mp4 -hls_time 10 -hls_list_size 0 -f hls pelicula.m3u8

The result shall be saved inside a folder, compressed and uploaded as a .zip file in the wordpress media uploader.
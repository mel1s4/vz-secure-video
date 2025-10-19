import { useRef, useState, useMemo, useEffect } from 'react';
import Plyr from 'plyr-react';
import Hls from 'hls.js';
import 'plyr-react/plyr.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import VideoInfoPanel from './VideoInfoPanel';
import './VideoPlayer.scss';

const VideoPlayer = ({ videoData }) => {
  const playerRef = useRef(null);
  const [isReady, setIsReady] = useState(false);

  // Plyr source configuration for video (supports both HLS and direct video)
  const plyrSource = useMemo(() => {
    if (!videoData?.file) {
      console.log('No video file in videoData');
      return null;
    }
    
    // Determine video type based on isHls flag or file extension
    let videoType = videoData.fileType || 'video/mp4';
    
    // If it's HLS, use the appropriate MIME type
    if (videoData.isHls) {
      videoType = 'application/x-mpegURL';
    }
    
    const source = {
      type: 'video',
      sources: [
        {
          src: videoData.file,
          type: videoType
        }
      ],
      title: videoData?.title || 'Video',
      poster: videoData?.thumbnail
    };
    
    console.log('Plyr source configured:', source);
    return source;
  }, [videoData]);

  // Plyr options
  const plyrOptions = useMemo(() => ({
    controls: [
      'play-large',
      'play',
      'progress',
      'current-time',
      'mute',
      'volume',
      'settings',
      'pip',
      'airplay',
      'fullscreen'
    ],
    settings: ['quality', 'speed'],
    quality: {
      default: 720,
      options: [4320, 2880, 2160, 1440, 1080, 720, 576, 480, 360, 240]
    },
    speed: {
      selected: 1,
      options: [0.25, 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2]
    },
    i18n: {
      restart: 'Restart',
      rewind: 'Rewind {seektime}s',
      play: 'Play',
      pause: 'Pause',
      fastForward: 'Forward {seektime}s',
      seek: 'Seek',
      seekLabel: '{currentTime} of {duration}',
      played: 'Played',
      buffered: 'Buffered',
      currentTime: 'Current time',
      duration: 'Duration',
      volume: 'Volume',
      mute: 'Mute',
      unmute: 'Unmute',
      enableCaptions: 'Enable captions',
      disableCaptions: 'Disable captions',
      download: 'Download',
      enterFullscreen: 'Enter fullscreen',
      exitFullscreen: 'Exit fullscreen',
      frameTitle: 'Player for {title}',
      captions: 'Captions',
      settings: 'Settings',
      pip: 'PIP',
      menuBack: 'Go back to previous menu',
      speed: 'Speed',
      normal: 'Normal',
      quality: 'Quality',
      loop: 'Loop',
      start: 'Start',
      end: 'End',
      all: 'All',
      reset: 'Reset',
      disabled: 'Disabled',
      enabled: 'Enabled',
      advertisement: 'Ad',
      qualityBadge: {
        2160: '4K',
        1440: 'HD',
        1080: 'HD',
        720: 'HD',
        576: 'SD',
        480: 'SD'
      }
    }
  }), []);

  // Initialize HLS.js for HLS video playback (only if HLS)
  useEffect(() => {
    let hls;
    
    // Only initialize HLS if this is an HLS video
    if (!videoData.isHls) {
      setIsReady(true);
      return;
    }
    
    // Wait for player to be ready
    const timer = setTimeout(() => {
      if (playerRef.current && plyrSource) {
        const player = playerRef.current.plyr;
        
        if (player && player.media) {
          if (Hls.isSupported()) {
            console.log('Initializing HLS.js...');
            
            const video = player.media;
            
            hls = new Hls({
              enableWorker: true,
              lowLatencyMode: true,
              backBufferLength: 90
            });
            
            hls.loadSource(videoData.file);
            hls.attachMedia(video);
            
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
              console.log('HLS manifest parsed successfully');
              setIsReady(true);
            });
            
            hls.on(Hls.Events.ERROR, (event, data) => {
              console.error('HLS error:', data);
            });
            
          } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            // Native HLS support (Safari)
            console.log('Using native HLS support (Safari)');
            setIsReady(true);
          }
        }
      }
    }, 500);
    
    return () => {
      clearTimeout(timer);
      if (hls) {
        hls.destroy();
      }
    };
  }, [plyrSource, videoData.file, videoData.isHls]);

  if (!plyrSource) {
    return (
      <div className="video-player-container">
        <div className="video-wrapper">
          <div className="no-video-message">
            <p>No video source available</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="video-player-container">
      <div className="video-wrapper">
        <Plyr
          ref={playerRef}
          source={plyrSource}
          options={plyrOptions}
        />
        {!isReady && (
          <div style={{ 
            position: 'absolute', 
            top: '50%', 
            left: '50%', 
            transform: 'translate(-50%, -50%)',
            color: 'white',
            fontSize: '1.2rem'
          }}>
            Loading video...
          </div>
        )}
      </div>

      <VideoInfoPanel videoData={videoData} />
    </div>
  );
};

export default VideoPlayer;

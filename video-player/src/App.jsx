import VideoPlayer from './components/VideoPlayer';
import { mockVideoData } from './mockData';
import './App.scss';

function App() {

	const videoData = window.vzVideoData || mockVideoData;

	return (
		<div className="app">
			<header className="app-header">
				<div className="header-content">
					<div className="logo">
						<i className="fas fa-play-circle"></i>
						<h1>Secure Video Player</h1>
					</div>
					<p className="header-subtitle">HLS Streaming with Custom Controls</p>
				</div>
			</header>

			<main className="app-main">
				<VideoPlayer videoData={videoData} />
			</main>

			<footer className="app-footer">
				<p>© 2024 Viroz Secure Video. Built with React & Video.js</p>
			</footer>
		</div>
	);
}

export default App;

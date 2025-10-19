import './VideoInfoPanel.scss';

const VideoInfoPanel = ({ videoData }) => {
	return (
		<div className="video-info-panel">
			<div className="video-description">
				<h4>Description</h4>
				<p>{videoData?.description || 'No description available.'}</p>
			</div>
			
			{videoData?.categories && (
				<div className="video-categories">
					<h4>Categories</h4>
					<div className="category-tags">
						{videoData.categories.split(',').map((cat, index) => (
							<span key={index} className="category-tag">{cat.trim()}</span>
						))}
					</div>
				</div>
			)}
		</div>
	);
};

export default VideoInfoPanel;


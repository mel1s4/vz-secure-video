import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Custom plugin to set correct MIME types for HLS files
const hlsMimeTypePlugin = () => {
	return {
		name: 'hls-mime-type',
		configureServer(server) {
			server.middlewares.use((req, res, next) => {
				if (req.url.endsWith('.m3u8')) {
					res.setHeader('Content-Type', 'application/vnd.apple.mpegurl');
				} else if (req.url.endsWith('.ts')) {
					res.setHeader('Content-Type', 'video/mp2t');
				}
				next();
			});
		}
	};
};

// https://vite.dev/config/
export default defineConfig({
	plugins: [react(), hlsMimeTypePlugin()],
	base: './', // Use relative paths for assets
	build: {
		assetsDir: 'assets',
		rollupOptions: {
			output: {
				assetFileNames: 'assets/[name]-[hash][extname]',
				chunkFileNames: 'assets/[name]-[hash].js',
				entryFileNames: 'assets/[name]-[hash].js'
			}
		}
	},
	server: {
		fs: {
			allow: ['..']
		}
	},
	preview: {
		fs: {
			allow: ['..']
		}
	}
})

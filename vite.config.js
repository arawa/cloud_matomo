
import { createAppConfig } from '@nextcloud/vite-config'
import fs from "fs";
import path from "path";
let root;

export default createAppConfig({
	// entry points: {name: script}
	admin: 'src/admin.ts',
	tracking: 'src/tracking.ts',
}, {
	config: {
		plugins: [
			{
				name: "clean-css-folder",
				apply: "build",
				configResolved(config) {
					root = config.root;
				},
				buildStart() {
					// clean all CSS
					const cssDir = path.resolve(root, "css");
					if (fs.existsSync(cssDir)) {
						fs.rmSync(cssDir, { recursive: true, force: true });
					}
				},
			}
		],
	},
})

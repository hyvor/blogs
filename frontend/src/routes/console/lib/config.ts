export interface Config {
	deployment: 'cloud' | 'on-prem';
	hyvor: {
		instance: string;
		hyvor_post_url: string;
		hyvor_talk_url: string;
	};
	domains: {
		app: string;
		delivery: string;
	};
	mercure: {
		public_url: string;
	};
	limits: {
		max_upload_size: number;
		max_theme_zip_size: number;
		max_asset_file_size: number;
	};
	highlight_themes: string[];
	ai_models: {
		value: string;
		provider: string;
		usage_percent: number;
	}[];
	services: {
		paddle: {
			sandbox: boolean;
			vendor_id: number;
		};
	};
}

let config = {} as Config;

export function setConfig(c: Config) {
	config = c;
}

export function getConfig() {
	return config;
}

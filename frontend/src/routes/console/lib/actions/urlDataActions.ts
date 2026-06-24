import consoleApi from '../consoleApi';

export function getUnfold<T extends 'embed' | 'link'>(url: string, type: T) {
	return consoleApi.get<
		T extends 'embed'
			? string
			: {
					url: string;
					final_url: string;
					title: string | null;
					description: string | null;
					thumbnail_url: string | null;
					icon_url: string | null;
					site_url: string | null;
				}
	>({
		endpoint: '/url-data',
		data: {
			url,
			type
		}
	});
}

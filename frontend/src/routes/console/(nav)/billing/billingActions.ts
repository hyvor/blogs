import consoleApi from '../../lib/consoleApi';

export interface Usage {
	used: number;
	limit: number;
}

export interface UsageData {
	users: Usage;
	storage: Usage;
	ai: Usage;
	blogs: Usage;
}

export function getUsage() {
	return consoleApi.get<UsageData>({
		endpoint: '/usage',
		userApi: true
	});
}

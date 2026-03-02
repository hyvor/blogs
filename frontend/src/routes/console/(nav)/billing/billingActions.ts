import consoleApi from '../../lib/consoleApi';

export interface Usage {
	used: number;
	limit: number;
}

export interface UsageData {
	users: Usage;
	storage: Usage;
	auto_translate_chars: Usage;
	ai_tokens: Usage;
}

export function getUsage() {
	return consoleApi.get<UsageData>({
		endpoint: '/usage',
		userApi: true,
		v2: true
	});
}

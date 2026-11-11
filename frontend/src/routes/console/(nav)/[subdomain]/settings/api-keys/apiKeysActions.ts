import consoleApi from '../../../../lib/consoleApi';
import type { ApiKey, ApiKeyType, Scope } from '../../../../lib/types';

export function createApiKey(name: string, type: ApiKeyType, scopes: Scope[]) {
	return consoleApi.post<ApiKey>({
		endpoint: '/api-key',
		data: {
			name,
			type,
			scopes
		}
	});
}

export function getApiKeys() {
	return consoleApi.get<ApiKey[]>({
		endpoint: '/api-keys'
	});
}

export function deleteApiKey(id: number) {
	return consoleApi.delete({
		endpoint: `/api-key/${id}`
	});
}

export function regenerateApiKey(id: number) {
	return consoleApi.patch<ApiKey>({
		endpoint: `/api-key/${id}`
	});
}

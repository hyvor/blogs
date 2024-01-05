import consoleApi from "../../../lib/consoleApi";
import type { ApiKey, ApiKeyType } from "../../../lib/types";


export function createApiKey(name: string, type: ApiKeyType) {
    return consoleApi.post<ApiKey>({
        endpoint: '/api-key',
        data: {
            name, type
        }
    })
}

export function getApiKeys() {
    return consoleApi.get<ApiKey[]>({
        endpoint: '/api-keys'
    })
}

export function deleteApiKey(id: number) {
    return consoleApi.delete({
        endpoint: `/api-key/${id}`
    })
}
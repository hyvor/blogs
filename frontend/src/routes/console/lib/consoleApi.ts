import { get } from 'svelte/store';
import { blogStore } from './stores/blogStore';
import { authOrganizationStore } from './stores';

export interface ConsoleApiOptions {
	endpoint: string;
	data?: Record<string, any> | FormData;
	userApi?: boolean;
	subdomain?: string;
	signal?: AbortSignal;
	raw?: boolean;
}

interface CallOptions extends ConsoleApiOptions {
	method: 'get' | 'post' | 'patch' | 'delete' | 'put';
}

export const CONSOLE_API_BASE_URL = '/api/console/v0';

export function getConsoleBlogBaseUrl(subdomain?: string) {
	const blogSubdomain = subdomain || get(blogStore).subdomain;
	return CONSOLE_API_BASE_URL + '/blog/' + blogSubdomain;
}

function getConsoleApi() {
	async function call<T>({
		endpoint,
		userApi = false,
		method,
		data = {},
		subdomain,
		signal,
		raw = false
	}: CallOptions): Promise<T> {
		if (!endpoint.startsWith('/')) endpoint = '/' + endpoint;

		let url;
		if (userApi) {
			url = CONSOLE_API_BASE_URL + endpoint;
		} else {
			url = getConsoleBlogBaseUrl(subdomain) + endpoint;
		}

		if (method === 'get') {
			url += '?' + objectToParams(data);
			/* 
            url += "?" + Object.entries(data)
                .filter(([_, val]) => val !== null && val !== undefined)
                .map(([key, val]) => key + '=' + encodeURIComponent(val))
                .join('&'); */
		}

		const headers = {} as Record<string, string>;

		const currentOrg = get(authOrganizationStore);

		if (currentOrg) {
			headers['X-Organization-Id'] = String(currentOrg.id);
		}

		if (!(data instanceof FormData)) {
			headers['Content-Type'] = 'application/json';
		}

		const options = {
			cache: 'no-cache',
			credentials: 'same-origin',
			method: method.toUpperCase(),
			headers,
			signal
		} as RequestInit;

		if (method !== 'get') {
			options.body = data instanceof FormData ? data : JSON.stringify(data);
		}

		const response = await fetch(url, options);

		if (!response.ok) {
			let e;
			try {
				e = await response.json();
			} catch (err) {
				e = null;
			}
			const error = e && e.message ? e.message : 'Something went wrong';

			const toThrow = new Error(error) as any;
			toThrow.message = error;
			toThrow.code = response.status;
			toThrow.data = e && e.data ? e.data : null;
			toThrow.body = e;

			throw toThrow;
		}

		if (raw) {
			return response as T;
		}

		const json = await response.json();
		return json as T;
	}

	return {
		call,
		get: async <T>(opt: ConsoleApiOptions) => call<T>({ ...opt, method: 'get' }),
		post: async <T>(opt: ConsoleApiOptions) => call<T>({ ...opt, method: 'post' }),
		patch: async <T>(opt: ConsoleApiOptions) => call<T>({ ...opt, method: 'patch' }),
		put: async <T>(opt: ConsoleApiOptions) => call<T>({ ...opt, method: 'put' }),
		delete: async <T>(opt: ConsoleApiOptions) => call<T>({ ...opt, method: 'delete' })
	};
}

// ChatGPT
function objectToParams(obj: object): string {
	const params = [];

	for (const key in obj) {
		if (obj.hasOwnProperty(key)) {
			const value = (obj as any)[key];

			if (value === null || value === undefined) {
				continue;
			}

			if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
				// Recurse into nested object
				params.push(objectToParams(value));
			} else if (Array.isArray(value)) {
				// Handle arrays
				for (let i = 0; i < value.length; i++) {
					params.push(`${encodeURIComponent(key)}[]=${encodeURIComponent(value[i])}`);
				}
			} else {
				params.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
			}
		}
	}

	return params.join('&');
}

const consoleApi = getConsoleApi();
export default consoleApi;

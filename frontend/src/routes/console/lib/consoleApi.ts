import { get } from 'svelte/store';
import { blogStore } from './stores/blogStore';
import { tempSubdomainStore } from './temp';
import { getCloudContext } from '@hyvor/design/cloud';

export interface ConsoleApiOptions {
	endpoint: string;
	data?: Record<string, any> | FormData;
	userApi?: boolean;
	v2?: boolean;
	subdomain?: string;
	signal?: AbortSignal;
}

interface CallOptions extends ConsoleApiOptions {
	method: 'get' | 'post' | 'patch' | 'delete' | 'put';
}

function getConsoleApi() {
	const baseUrl = '/api/console/v0';
	const baseUrlV2 = '/api/v2/console';

	async function call<T>({
		endpoint,
		userApi = false,
		v2 = false,
		method,
		data = {},
		subdomain,
		signal
	}: CallOptions): Promise<T> {
		if (!endpoint.startsWith('/')) endpoint = '/' + endpoint;

		let url;
		if (v2) {
			url = baseUrlV2 + endpoint;
		} else if (userApi) {
			url = baseUrl + endpoint;
		} else {
			const blogSubdomain = subdomain || get(blogStore).subdomain;
			url = baseUrl + '/blog/' + blogSubdomain + endpoint;
		}

		if (method === 'get') {
			url += '?' + objectToParams(data);
			/* 
            url += "?" + Object.entries(data)
                .filter(([_, val]) => val !== null && val !== undefined)
                .map(([key, val]) => key + '=' + encodeURIComponent(val))
                .join('&'); */
		}

		const headers = {
			'X-TEMP-SUBDOMAIN': get(tempSubdomainStore),
		} as Record<string, string>;

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
			const e = await response.json();
			const error = e && e.error ? e.error : 'Something went wrong';
			/* toast({type: 'error', message: error});
            throw error; */

			const toThrow = new Error(error) as any;
			toThrow.message = error;
			toThrow.code = response.status;
			toThrow.data = e && e.data ? e.data : null;

			throw toThrow;
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

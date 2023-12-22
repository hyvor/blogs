import { get } from "svelte/store";
import { blogStore } from "./stores";
// import { currentProjectIdStore } from "./stores";

export const APP_URL = import.meta.env.VITE_APP_URL || location.origin;

interface Options {
    endpoint: string,
    data?: Record<string, any> | FormData,
    userApi?: boolean,
    subdomain?: string,
}

interface CallOptions extends Options {
    method: 'get' | 'post' | 'patch' | 'delete'
}

function getConsoleApi() {

    const baseUrl = APP_URL + "/api/console/v0";

    async function call<T>({ 
        endpoint, 
        userApi = false, 
        method, 
        data = {},
        subdomain,
    }: CallOptions) : Promise<T> {

        if (!endpoint.startsWith('/'))
            endpoint = '/' + endpoint;

        // const projectId = get(currentProjectIdStore);
        // let url = baseUrl + (projectApi ? "/project/" + projectId : "") + endpoint;

        let url;
        if (userApi) {
            url = baseUrl + endpoint;
        } else {
            const blogSubdomain = subdomain || get(blogStore).subdomain;
            url = baseUrl + "/blog/" + blogSubdomain + endpoint;
        }

        if (method === 'get') {

            url += "?" + Object.entries(data)
                .filter(([_, val]) => val !== null && val !== undefined)
                .map(([key, val]) => key + '=' + encodeURIComponent(val))
                .join('&');

        }

        const headers = {
            'Content-Type': data instanceof FormData ?
                    'application/x-www-form-urlencoded' :
                    'application/json',
        } as Record<string, string>;

        const options = {
            cache: 'no-cache',
            credentials: 'same-origin',
            method: method.toUpperCase(),
            headers
        } as RequestInit;

        if (method !== 'get') {
            options.body = data instanceof FormData ? data : JSON.stringify(data);
        }

        const response = await fetch(url, options)

        if (!response.ok) {
            const e = await response.json();
            const error = e && e.error ? e.error : 'Something went wrong';
            /* toast({type: 'error', message: error});
            throw error; */

            const toThrow = new Error(error) as any; 
            toThrow.code = e && e.code ? e.code : 500;

            throw toThrow;
        }

        const json = await response.json();
        return json as T;

    }

    return {
        call,
        get: async <T>(opt: Options) => call<T>({...opt, method: 'get'}),
        post: async <T>(opt: Options) => call<T>({...opt, method: 'post'}),
        patch: async <T>(opt: Options) => call<T>({...opt, method: 'patch'}),
        delete: async <T>(opt: Options) => call<T>({...opt, method: 'delete'}),
    }

}

const consoleApi = getConsoleApi();
export default consoleApi;
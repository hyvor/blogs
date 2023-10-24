
/**
 * Call the Console API via Axois
 */

import axios, {AxiosError} from "axios";
import {toast} from "react-toastify";

export function getUserEndpoint(endpoint: string) {
    return '/api/console/v0' + endpoint;
}

export function getMiscEndpoint(endpoint: string) {
    return '/api/console/v0/misc' + endpoint
}

export function getEndpoint(subdomain: string, endpoint: string) {
    return '/api/console/v0/blog/' + subdomain + endpoint
}

const api = {

    get: async <T> (subdomain: string, endpoint: string, params = {}) : Promise<T> => {
        try {
            const res = await axios.get(getEndpoint(subdomain, endpoint), {params});
            return res.data as T;
        } catch (e: any) {
            toast.error(e.response.data.error)
            if (e.response.status !== 422)
                throw new Error(e.response.data.error);
        }
        return {} as T;
    },

    post: async <T> (subdomain: string, endpoint: string, data = {}) : Promise<T> => {
        try {
            const res = await axios.post(getEndpoint(subdomain, endpoint), data);
            return res.data as T;
        } catch (e: any) {
            toast.error(e.response.data.error)
            throw new Error(e.response.data.error)
        }
    },

    delete: async <T> (subdomain: string, endpoint: string, data = {}) : Promise<T> => {
        try {
            const res = await axios.delete(getEndpoint(subdomain, endpoint), {data});
            return res.data as T;
        } catch (e: any) {
            toast.error(e.response.data.error)
            throw new Error(e.response.data.error)
        }
    },

    patch: async <T> (subdomain: string, endpoint: string, data = {}) : Promise<T> => {
        try {
            const res = await axios.patch(getEndpoint(subdomain, endpoint), data);
            return res.data as T;
        } catch (e: any) {
            toast.error(e.response.data.error)
            throw new Error(e.response.data.error)
        }
    },

    put: async <T> (subdomain: string, endpoint: string, data = {}) : Promise<T>  => {
        try {
            const res = await axios.put(getEndpoint(subdomain, endpoint), data);
            return res.data as T;
        } catch (e: any) {
            toast.error(e.response.data.error)
            throw new Error(e.response.data.error)
        }
    }

}

export default api;
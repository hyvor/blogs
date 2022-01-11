
/**
 * 
 * Call the Console API via Axois
 */

import axios from "axios";

function getEndpoint(subdomain, endpoint) {
    return '/api/console/v0/blog/' + subdomain + endpoint
}

const api = {

    get: async (subdomain, endpoint, params = {}) => {
        const res = await axios.get(getEndpoint(subdomain, endpoint), {params});
        return res.data;
    },

    post: async (subdomain, endpoint, data = {}) => {
        const res = await axios.post(getEndpoint(subdomain, endpoint), data);
        return res.data;
    },

    delete: async (subdomain, endpoint, data = {}) => {
        const res = await axios.delete(getEndpoint(subdomain, endpoint), data);
        return res.data;
    },

    patch: async (subdomain, endpoint, data = {}) => {
        const res = await axios.patch(getEndpoint(subdomain, endpoint), data);
        return res.data;
    }

}

export default api;

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
    }

}

export default api;
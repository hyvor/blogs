/**
 * Application-level helpers
 */

/**
 * 
 * @param {string} subdomain 
 * @param {string} endpoint with trailing slash
 * @returns 
 */
export function getConsoleAPIEndpoint(subdomain, endpoint) {

    return '/api/console/v0/blog/' + subdomain + endpoint

}
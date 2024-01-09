
export function isSubdomainValid(subdomain: string) : null | string {

    subdomain = subdomain.trim();

    const allowedRegex = /[^a-z0-9-]/;

    if (subdomain.length === 0) {
        return 'Cannot be empty';
    } if (subdomain.substring(0, 1) === '-') {
        return 'Cannot start with -';
    } else if (subdomain.substring(subdomain.length - 1) === '-') {
        return 'Cannot end with -';
    } else if (subdomain.match(allowedRegex)) {
        const match = subdomain.match(allowedRegex)
        const firstLetter = match ? match[0] : '';
        return 'Cannot contain ' + firstLetter;
    }

    return null;

}
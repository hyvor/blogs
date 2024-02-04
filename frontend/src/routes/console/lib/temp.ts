import { writable } from "svelte/store";

export const LOCAL_STORAGE_KEY = 'console-temp-subdomain';

export const isTempStore = writable(false);

export const tempSubdomainStore = writable<string | null>(null);

export function initTempSubdomain() {
    const data = localStorage.getItem(LOCAL_STORAGE_KEY);

    if (!data) {
        return null;
    }

    let subdomain = null;
    let timestamp = null;

    try {
        const parsed = JSON.parse(data) || {};

        subdomain = parsed.subdomain;
        timestamp = parsed.timestamp;
    } catch (e) {
        return null;
    }

    if (!subdomain || !timestamp) {
        return null;
    }

    const now = Date.now();
    const diff = now - timestamp;

    if (diff > 1000 * 60 * 60 * 24) {
        localStorage.removeItem(LOCAL_STORAGE_KEY);
        return null;
    }

    tempSubdomainStore.set(subdomain);

    return subdomain;
}

export function setTempSubdomain(subdomain: string) {
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify({
        subdomain,
        timestamp: Date.now()    
    }));
    tempSubdomainStore.set(subdomain);
}
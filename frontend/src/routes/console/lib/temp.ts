import { writable } from "svelte/store";

export const LOCAL_STORAGE_KEY = 'console-temp-subdomain';

export const isTempStore = writable(false);

export const tempSubdomainStore = writable<string | null>(null);

export function initTempSubdomain() {
    const subdomain = sessionStorage.getItem(LOCAL_STORAGE_KEY);
    if (subdomain) {
        tempSubdomainStore.set(subdomain);
    }
    return subdomain;
}

export function setTempSubdomain(subdomain: string) {
    sessionStorage.setItem(LOCAL_STORAGE_KEY, subdomain);
    tempSubdomainStore.set(subdomain);
}
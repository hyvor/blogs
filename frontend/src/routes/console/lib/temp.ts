import { writable } from "svelte/store";

export const LOCAL_STORAGE_KEY = 'console-temp-unique-id';

export const isTempStore = writable(false);

export const tempUniqueIdStore = writable<string | null>(null);

export function initTempUniqueId() {
    const uniqueId = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (uniqueId) {
        tempUniqueIdStore.set(uniqueId);
    }
    return uniqueId;
}
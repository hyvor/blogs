import { get, writable } from "svelte/store";
import type { Language } from "../types";

// current blog's languages
export const languagesStore = writable<Language[]>([]);


export function getPrimaryLanguage() {
    return get(languagesStore).find(l => l.is_primary)!;
}
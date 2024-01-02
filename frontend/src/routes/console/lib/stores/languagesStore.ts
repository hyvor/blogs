import { derived, get, writable } from "svelte/store";
import type { Language } from "../types";

// current blog's languages
export const languagesStore = writable<Language[]>([]);


export const primaryLanguageStore = derived(languagesStore, $languages => {
    return $languages.find(l => l.is_primary)!;
});


export function getPrimaryLanguage() {
    return get(languagesStore).find(l => l.is_primary)!;
}
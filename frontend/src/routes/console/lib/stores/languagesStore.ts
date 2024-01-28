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

export function languageStoreAdd(lang: Language) {
    languagesStore.update(langs => [...langs, lang]);
}

export function languageStoreUpdate(lang: Language) {
    languagesStore.update(langs => langs.map(l => lang.id === l.id ? lang : l));
}

export function languageStoreRemove(id: number) {
    languagesStore.update(langs => langs.filter(l => l.id !== id));
}
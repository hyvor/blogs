import { get } from "svelte/store";
import { languagesStore } from "../stores";

export function getLanguageById(id: number) {
    return get(languagesStore).find(l => l.id === id);
}
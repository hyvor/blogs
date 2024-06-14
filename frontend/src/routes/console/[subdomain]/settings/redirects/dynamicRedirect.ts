import { writable } from "svelte/store";

export const dynamicRedirectsStore = writable<number>(0);
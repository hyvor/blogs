import { writable } from 'svelte/store';

export const tab = writable<'settings' | 'seo' | 'links' | 'ai'>('settings');

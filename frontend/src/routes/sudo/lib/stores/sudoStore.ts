import { writable } from 'svelte/store';
import type { SudoConfig, SudoStats } from '../../types';

export const configStore = writable<SudoConfig>();
export const statsStore = writable<SudoStats>({
	total_blogs: 0,
	total_30d_change: 0,
	blogs_with_custom_domains: 0
});

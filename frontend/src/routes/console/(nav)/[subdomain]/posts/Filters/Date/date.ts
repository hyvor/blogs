import { writable } from 'svelte/store';

/**
 * Maps each date filter option to its locale string key.
 */
export const OPTIONS = {
	today: 'console.posts.filters.dateOptions.today',
	last_week: 'console.posts.filters.dateOptions.last_week',
	last_month: 'console.posts.filters.dateOptions.last_month',
	last_year: 'console.posts.filters.dateOptions.last_year'
	// custom: 'console.posts.filters.dateOptions.custom'
} as const;

export const dateFilterStore = writable<null | keyof typeof OPTIONS>(null);

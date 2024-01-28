import { writable } from "svelte/store";

export const OPTIONS = {
    today: 'Today',
    last_week: 'Last week',
    last_month: 'Last month',
    last_year: 'Last year',
    // custom: 'Custom'
};

export const dateFilterStore = writable<null | keyof typeof OPTIONS>(null);
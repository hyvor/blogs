import { writable } from "svelte/store";

export interface Feature {
    name: string,
    description?: string,
    values: (string | number | boolean)[],
}

export const plansMax = writable(3);
export const plansStart = writable(0);


export const PLANS = [
    {name: 'Starter', price: 9},
    {name: 'Growth', price: 19},
    {name: 'Premium', price: 49},
    {name: 'Team', price: 299},
    {name: 'Business', price: 699},
    {name: 'Enterprise', price: 1299},
]
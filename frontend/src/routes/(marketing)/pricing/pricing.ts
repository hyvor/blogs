import { writable } from "svelte/store";

export interface Feature {
  name: string;
  under?: string;
  description?: string;
  values: (string | number | boolean)[];
}

export const plansMax = writable(3);
export const plansStart = writable(0);

export const PLANS = [
  { name: "Starter", price: 12 },
  { name: "Growth", price: 40 },
  { name: "Premium", price: 125 },
];

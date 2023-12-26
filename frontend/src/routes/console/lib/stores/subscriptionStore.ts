import { get, writable } from "svelte/store";
import type { Subscription } from "../types";
import { blogStore } from "../stores";
import dayjs from "dayjs";

export const subscriptionStore = writable<Subscription | null>();

export function isInTrial() {
    const blog = get(blogStore);
    return blog.trial_ends_at > dayjs().unix();
}
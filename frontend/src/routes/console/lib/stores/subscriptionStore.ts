import { get } from "svelte/store";
import { blogStore } from "./blogStore";
import dayjs from "dayjs";

/**
 * @deprecated
 */
export function isInTrial() {
    const blog = get(blogStore);
    return blog.trial_ends_at > dayjs().unix();
}
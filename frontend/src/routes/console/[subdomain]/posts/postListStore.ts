import { writable } from "svelte/store";
import type { PostStatus, Tag, User } from '../../lib/types';

interface PostListFilters {
    status: PostStatus | 'featured' | null,
    author: User | null,
    tag: Tag | null,
    startDate: Date | null,
    endDate: Date | null,
    search: string | null
}

export const postListFiltersStore = writable<PostListFilters>({
    status: null,
    author: null,
    tag: null,
    startDate: null,
    endDate: null,
    search: null
})

export function setFilter<K extends keyof PostListFilters>(name: K, value: PostListFilters[K]) {
    postListFiltersStore.update((filters) => {
        return {...filters, [name]: value}
    })
}

import { get } from "svelte/store";
import { postOriginalStore, postOriginalVariantStore, postStore, postVariantStore } from "../../../../postStore";
import type { Post, PostVariant } from "../../../../../../lib/types";


export function getPublishedChanges() {

    // checks the post and the current variant for changes

    const post = get(postStore);
    const postOriginal = get(postOriginalStore);

    const postVariant = get(postVariantStore);
    const postVariantOriginal = get(postOriginalVariantStore);

    const changes = {
        post: {} as Partial<Post>,
        variant: {} as Partial<PostVariant>,
    }

    const postKeys: (keyof Post)[] = [
        'published_at', 'is_featured', 
        'featured_image_url',
        'canonical_url',
        'code_head',
        'code_foot'
    ];

    const postVariantKeys: (keyof PostVariant)[] = [
        'slug',
        'content',
        'title',
        'description',
    ];

    postKeys.forEach(key => {
        if (post[key] !== postOriginal[key]) {
            // @ts-ignore
            changes.post[key] = post[key];
        }
    });

    postVariantKeys.forEach(key => {
        if (postVariant[key] !== postVariantOriginal[key]) {
            // @ts-ignore
            changes.variant[key] = postVariant[key];
        }
    });

    return changes;

}

export function hasPublishedChanges() {
    const changes = getPublishedChanges();
    return Object.keys(changes.post).length > 0 
        || Object.keys(changes.variant).length > 0;
}
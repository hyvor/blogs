import { get } from "svelte/store";
import { postOriginalStore, postOriginalVariantStore, postStore, postVariantStore, updatePostEditingStatusValue, updatePostVariantStore } from "../../../../postStore";
import type { Post, PostVariant } from "../../../../../../lib/types";
import { hasIdArrayChanged } from "../../Settings/settingsHelpers";


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

    if (hasIdArrayChanged(post.tags, postOriginal.tags)) {
        changes.post.tags = post.tags;
    }
    if (hasIdArrayChanged(post.authors, postOriginal.authors)) {
        changes.post.authors = post.authors;
    }

    if (
        postVariant.content_unsaved !== null &&
        postVariant.content_unsaved !== postVariantOriginal.content
    ) {
        changes.variant.content = postVariant.content_unsaved;
    }

    return changes;

}

export function hasPublishedChanges() {
    const changes = getPublishedChanges();
    return Object.keys(changes.post).length > 0 
        || Object.keys(changes.variant).length > 0;
}


export function finishUpdating() {

    // no longer editing
    updatePostEditingStatusValue('isEditingPublished', false);

    // clear unsaved content
    updatePostVariantStore({content_unsaved: null});

}
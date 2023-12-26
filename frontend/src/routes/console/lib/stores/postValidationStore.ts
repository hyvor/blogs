import { derived, writable, type Writable } from "svelte/store";
import { postVariantStore } from "./postStore";

function validateSlug(slug: string|null) : null|string {

    if (slug === null) {
        return null;
    }

    if (slug.length > 255) {
        return 'Slug is too long';
    } else if (slug.includes('/')) {
        return 'Slug cannot contain /';
    }

    return null;

}

export const postVariantValidationStore = writable({
    slug: null as null | string,
})

postVariantStore.subscribe(postVariant => {
    postVariantValidationStore.update(validation => {
        return {
            ...validation,
            slug: validateSlug(postVariant.slug),
        }
    })
});
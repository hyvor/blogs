import { error } from "@sveltejs/kit";
import { items } from "./docs";

export async function load({ params }) {

    const slug = params.slug;
    const item = slug === undefined ? items[0] : items.find(item => item.slug === slug);

    if(!item) {
        error(404, 'Not found');
    }

    return {
        slug: params.slug,
        name: item.name,
        component: item.component
    }
}
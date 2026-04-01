import { get } from "svelte/store";
import type { Blog } from "../types";
import { configStore } from "./stores/sudoStore";


export function getHostingUrl(blog: Blog): string {
    const config = get(configStore);

    if (blog.hosting_at === 'domain') {
        return 'https://' + blog.hosting_domain;
    }
    if (blog.hosting_at === 'self' && blog.hosting_url) {
        return blog.hosting_url;
    }

    const deliveryUrl = new URL(config.app.delivery_url);

    return deliveryUrl.protocol + '//' + blog.subdomain + '.' + deliveryUrl.hostname;
}
import { get } from "svelte/store";
import { isTempStore } from "./temp";
import { blogStore } from "./stores/blogStore";


export function consoleUrl(path: string) {

    path = path.replace(/^\//, '');

    const isTemp = get(isTempStore);

    return '/console/' 
        + path
        + (isTemp ? '?temp' : '');

}


export function consoleUrlWithBlog(path: string) {
    const blogSubdomain = get(blogStore).subdomain;
    path = path.replace(/^\//, '');
    return consoleUrl(`${blogSubdomain}/${path}`)
}
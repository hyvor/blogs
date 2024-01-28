import { get } from "svelte/store";
import { LINK_STATUS, type Link } from "../../../../../lib/links/links";
import { callLinkAnalysisApi } from "../../../../tools/link-analysis/linkAnalysisActions";
import { variantLinkAnalysisStore } from "./linksStore";
import { postVariantStore, updatePostVariantStore } from "../../../postStore";
import type { LinkAnalysisLink } from "../../../../../lib/types";

export function initLoader() {
    variantLinkAnalysisStore.subscribe(handleChange);
}

let lastLoadedLinks: null | string = null;

function handleChange(analysis: Record<string, number>) {
    const loadingLinks = Object.keys(analysis).filter(link => analysis[link] === LINK_STATUS.LOADING);

    if (loadingLinks.length === 0)
        return;

    if (lastLoadedLinks === loadingLinks.join(','))
        return;

    lastLoadedLinks = loadingLinks.join(',');

    callLinkAnalysisApi(
        get(postVariantStore).id,
        loadingLinks
    ).then(res => {

        updatePostVariantStore({
            'link_analysis': {
                ...analysis,
                ...getResultObjectFromLinks(res),
            }
        });

    }).catch(() => {

        updatePostVariantStore({
            'link_analysis': {
                ...analysis,
                ...loadingLinks.reduce((acc, link) => {
                    acc[link] = LINK_STATUS.ERROR;
                    return acc;
                }, {} as Record<string, number>)
            }
        });

    });

}

function getResultObjectFromLinks(links: LinkAnalysisLink[]) {
    const obj : Record<string, number> = {}
    links.forEach(link => {
        obj[link.url] = link.ignored ? LINK_STATUS.IGNORED : link.status_code;
    })
    return obj;
}
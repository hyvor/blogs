import { writable } from "svelte/store";
import consoleApi from "../../../../lib/consoleApi";
import type { LinkAnalysisCheck, LinkAnalysisLink } from "../../../../lib/types";

export interface Stats {
    counts: {
        ok: number,
        broken: number,
        redirect: number,
        ignored: number
    }
}

export function getStats() {
    return consoleApi.get<Stats>({
        endpoint: '/link-analysis/stats'
    });
}


export type FilterType = null | 'ok' | 'broken' | 'redirect' | 'ignored';

interface GetLinksData {
    type: FilterType,
    limit?: number,
    offset?: number,
}

export function getLinks(data: GetLinksData) {
    return consoleApi.get<LinkAnalysisLink[]>({
        endpoint: '/link-analysis/links',
        data
    });
}


export const linkAnalysisChecks = writable<LinkAnalysisCheck[]>([]);

export function getChecks() {
    return consoleApi.get<LinkAnalysisCheck[]>({
        endpoint: '/link-analysis/checks'
    });
}


export function startFullAnalysis() {
    return consoleApi.post<LinkAnalysisCheck>({
        endpoint: '/link-analysis/check',
    });
}

export function callLinkAnalysisApi(
    postVariantId: number,
    urls: string[], // originalUrls
    // force: boolean = false
) {
    
    return consoleApi.post<LinkAnalysisLink[]>({
        endpoint: '/link-analysis/check-urls',
        data: {
            post_variant_id: postVariantId,
            urls
        }
    });

}

export function callIgnoreLink(postVariantId: number, url: string, status: boolean) {

    return consoleApi.patch<LinkAnalysisLink>({
        endpoint: '/link-analysis/ignore-link',
        data: {
            post_variant_id: postVariantId,
            url,
            status: status ? 1 : 0,
        }
    });

}
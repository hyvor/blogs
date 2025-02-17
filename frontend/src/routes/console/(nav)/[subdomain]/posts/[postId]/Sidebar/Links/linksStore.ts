import { derived } from "svelte/store";
import { postCurrentContentStore, postVariantStore } from "../../../postStore";
import { blogStore } from "../../../../../../lib/stores/blogStore";
import { calculateLinkAnalysis, getLinksFromContent, getStatusType, LINK_STATUS, type Link } from "../../../../../../lib/links/links";

export const variantLinksStore = derived(
    [postCurrentContentStore, blogStore],
    ([content, blog]) => getLinksFromContent(content, blog.url)
);

export const variantLinkAnalysisStore = derived(
    [postVariantStore],
    ([variant]) => calculateLinkAnalysis(variant)
);

export const variantLinkCountsStore = derived(
    [variantLinkAnalysisStore, variantLinksStore],
    ([analysis, links]) => getLinkCounts(analysis, links)
);

function getLinkCounts(analysis: Record<string, number>, links: Link[]) {

    let okCount = 0;
    let redirectCount = 0;
    let brokenCount = 0;
    let riskyCount = 0;
    let ignoreCount = 0;
    let loadingCount = 0;

    links.forEach(link => {

        const status = analysis[link.originalHref] || LINK_STATUS.ERROR;
        const statusType = getStatusType(status);

        if (statusType === 'ok') {
            okCount++;
        } else if (statusType === 'redirect') {
            redirectCount++;
        } else if (statusType === 'broken') {
            brokenCount++;
        } else if (statusType === 'risky') {
            riskyCount++;
        } else if (statusType === "ignored") {
            ignoreCount++;
        } else if (statusType === "loading") {
            loadingCount++;
        }

    });

    return {
        total: links.length,
        ok: okCount,
        redirect: redirectCount,
        broken: brokenCount,
        risky: riskyCount,
        ignored: ignoreCount,
        loading: loadingCount,
    }

}
import { EditorView } from "prosemirror-view";
import { getDocFromContent, positionSelectionInMiddleOfScreen } from "../../ProseMirror/helpers";
import { TextSelection } from "prosemirror-state";
import { usePostActions, usePostValues } from "../../helpers";
import { PostVariant } from "../../../../types";
import { getUserBlogBlog, useUserBlog } from "../../../../logic-helpers/blog";
import { useEffect, useRef } from "react";
import api from "../../../../lib/api";
import getSubdomain from "../../../../logic-helpers/subdomain";
import {parse} from 'tldts';

export const LINK_STATUS = {
    LOADING: -1,
    IGNORED: -2,
    ERROR: -3
};

type LinkType = 
    'internal-blog' | // inside the blog
    'internal-domain' | // not inside the blog, but inside the domain
    'internal-root-domain' | // not inside the blog or subdomain, but inside the root domain
    'external' | // outside the root domain
    'mail' | // mailto:
    'tel' | // tel:
    'anchor' | // #anchor
    'other'; // anything else

export interface Link {
    index: number,
    type: LinkType,
    href: string,
    anchor: string | null,

    pos: number,
}

function isValidUrlAnyProtocol(url: string) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}

export function getLinksFromContent(content: string | null, blogUrl: string) : Link[] {

    const doc = getDocFromContent(content);
    const links: Link[] = [];

    doc.descendants((node, pos) => {

        const marks = node.marks;
        if (marks.length === 0) return;

        node.marks.forEach((mark, i) => {

            if (mark.type.name !== 'link') return;
            const href = mark.attrs.href;
            if (!href) return;

            const type = getLinkType(href, blogUrl);
            if (!type) return;

            links.push({
                index: links.length,
                type,
                href: 
                    isValidUrlAnyProtocol(href) ? 
                        href : // absolute url
                        getFullUrl(href, blogUrl).toString(),
                anchor: node.textContent ? node.textContent : null,

                pos,
            });

        });

    });

    return links;

}

export function getLinkType(href: string, blogUrl: string) : LinkType {

    if (href.startsWith('mailto:')) return 'mail';
    if (href.startsWith('tel:')) return 'tel';
    if (href.startsWith('#')) return 'anchor';

    const baseUrl = blogUrl.endsWith('/') ? blogUrl : blogUrl + '/';
    const hrefFullUrl = getFullUrl(href, blogUrl);

    if (hrefFullUrl.toString().startsWith(baseUrl)) return 'internal-blog';
    if (hrefFullUrl.protocol !== 'https:' && hrefFullUrl.protocol !== 'http:') return 'other';

    const { domain: blogDomain, subdomain: blogSubdomain } = parse(blogUrl);
    const { domain: hrefDomain, subdomain: hrefSubdomain } = parse(hrefFullUrl.toString());

    if (blogDomain === hrefDomain) {
        return blogSubdomain === hrefSubdomain ? 
            'internal-domain' : 
            'internal-root-domain'; 
    } else {
        return 'external';
    }

}

export function focusLinkInEditor(link: Link, editorView: EditorView) {

    const doc = editorView.state.doc;
    const pos = link.pos;

    const resolvedPos = doc.resolve(pos);
    const selection = TextSelection.create(doc, pos, pos + resolvedPos.nodeAfter!.nodeSize);

    editorView.dispatch(
        editorView.state.tr
            .setSelection(selection)
            .scrollIntoView()
    );
    editorView.focus();

    positionSelectionInMiddleOfScreen(editorView);

}

export function getFullUrl(href: string, blogUrl: string) : URL {
    const baseUrl = blogUrl.endsWith('/') ? blogUrl : blogUrl + '/';
    return new URL(href, baseUrl);
}

export function calculateLinkAnalysis(variant: PostVariant) : Record<string, number> {

    const {base_url: baseUrl} = getUserBlogBlog();

    const content = variant.content_unsaved || variant.content;
    const links = getLinksFromContent(content, baseUrl);

    const linkStatuses: Record<string, number> = {};
    const linkAnalysis = variant.link_analysis || {};

    links.forEach((link, i) => {

        let status = linkAnalysis[link.href] || LINK_STATUS.LOADING;

        if (link.type === 'anchor') {

            const doc = getDocFromContent(content);
            const anchorId = link.href.replace('#', '').trim();

            let found = false;

            doc.descendants((node, pos) => {
                if (node.type.name !== 'heading') return;
                if (found) return;
                if (node.attrs.id === anchorId) {
                    found = true;
                }
            });

            status = found ? 200 : 404;

        } else if (!isHttpLink(link)) {
            status = LINK_STATUS.IGNORED;
        }

        // ignored after 100 links
        if (i >= 100) {
            status === LINK_STATUS.IGNORED;
        }

        linkStatuses[link.href] = status;
    })

    return linkStatuses;
}

export function isHttpLink(link: Link) {
    return link.type === 'internal-blog' ||
        link.type === 'internal-domain' ||
        link.type === 'internal-root-domain' ||
        link.type === 'external';
}

export function getStatusType(status: number) : 
    'loading' | 'ok' | 'redirect' | 'broken' | 'ignored' | 'error'
{
    if (status === LINK_STATUS.LOADING) return 'loading';
    if (status === LINK_STATUS.IGNORED) return 'ignored';
    if (status === LINK_STATUS.ERROR) return 'error';
    if (status >= 200 && status < 300) return 'ok';
    if (status >= 300 && status < 400) return 'redirect';
    return 'broken';
}

export function useUpdateLinkAnalysis(id: number) {

    const subdomain = getSubdomain();
    const { updateCurrentPostVariantValue } = usePostActions(id);
    const { currentVariant, currentVariantLinkAnalysis } = usePostValues(id);
    const { blog: {base_url: baseUrl} } = useUserBlog();

    const content = currentVariant.content_unsaved || currentVariant.content;
    const links = getLinksFromContent(content, baseUrl);
    let linksCount = links.length;

    let okCount = 0;
    let redirectCount = 0;
    let brokenCount = 0;
    let ignoreCount = 0;
    let loadingCount = 0;

    links.forEach(link => {
        const status = currentVariantLinkAnalysis[link.href];
        const statusType = getStatusType(status);

        if (statusType === "ok") {
            okCount++;
        } else if (statusType === "redirect") {
            redirectCount++;
        } else if (statusType === "broken") {
            brokenCount++;
        } else if (statusType === "ignored") {
            ignoreCount++;
        } else if (statusType === "loading") {
            loadingCount++;
        }
    });

    const lastLoadedLinksRef = useRef<string>('');

    useEffect(() => {

        const loadingLinks : string[] = [];

        for (const link in currentVariantLinkAnalysis) {
            if (currentVariantLinkAnalysis[link] === LINK_STATUS.LOADING) {
                loadingLinks.push(link);
            }
        }

        if (lastLoadedLinksRef.current === loadingLinks.join(',')) return;
        if (loadingLinks.length === 0) return;

        callLinkAnalysisApi(id, currentVariant.language_id, loadingLinks)
            .then(res => {
                updateCurrentPostVariantValue('link_analysis', {
                    ...currentVariantLinkAnalysis,
                    ...res,
                })
            })
            .catch(() => {

                updateCurrentPostVariantValue('link_analysis', loadingLinks.reduce((acc, link) => {
                    acc[link] = LINK_STATUS.ERROR;
                    return acc;
                }, {} as Record<string, number>));

            });

        lastLoadedLinksRef.current = loadingLinks.join(',');

    }, [currentVariantLinkAnalysis]);

    return {

        reloadLink: (link: Link, onReload: (status: number) => void) => {

            callLinkAnalysisApi(
                id, 
                currentVariant.language_id, 
                [link.href], 
                true
            ).then(res => {

                const status = res[link.href];

                updateCurrentPostVariantValue('link_analysis', {
                    ...currentVariantLinkAnalysis,
                    [link.href]: status,
                })

                onReload(status);

            })

        },

        reloadAllLinks: (onReload: Function) => {

            /* updateCurrentPostVariantValue('link_analysis', {})
            return; */

            const allLinks = links
                .filter(link => isHttpLink(link))
                .map(link => link.href)

            callLinkAnalysisApi(
                id,
                currentVariant.language_id,
                allLinks,
                true,
            ).then(res => {  
                updateCurrentPostVariantValue('link_analysis', {
                    ...currentVariantLinkAnalysis,
                    ...res,
                })
            })
            .finally(() => {
                onReload();
            })

        },

        ignoreLink: (link: Link, status: boolean = true) => {
            
            return callIgnoreLink(link.href, status)
                .then(data => {
                    updateCurrentPostVariantValue('link_analysis', {
                        ...currentVariantLinkAnalysis,
                        [link.href]: data.status
                    })
                })

        },

        counts: {
            total: linksCount,
            ok: okCount,
            redirect: redirectCount,
            broken: brokenCount,
            ignored: ignoreCount,
            loading: loadingCount,
        }

    }

}

function callLinkAnalysisApi(
    postId: number, 
    languageId: number, 
    urls: string[],
    force: boolean = false
) {

    const subdomain = getSubdomain();

    return api.post<Record<string, number>>(subdomain, '/link-analysis/variant', {
        post_id: postId,
        language_id: languageId,
        urls,
        force: force ? 1 : 0,
    });

}

function callIgnoreLink(url: string, status: boolean) {

    const subdomain = getSubdomain();
    return api.patch<{status: number}>(subdomain, '/link-analysis/ignore-link', {
        url,
        status: status ? 1 : 0,
    });

} 
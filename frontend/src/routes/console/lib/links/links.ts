import { EditorView } from 'prosemirror-view';
import { getDocFromContent, positionSelectionInMiddleOfScreen } from '$lib/Prosemirror/helpers';
import { TextSelection } from 'prosemirror-state';
import { parse } from 'tldts';
import type { PostVariant } from '../types';

export const LINK_STATUS = {
	LOADING: -1,
	IGNORED: -2,
	ERROR: -3
};

type LinkType =
	| 'internal-blog' // inside the blog
	| 'internal-domain' // not inside the blog, but inside the domain
	| 'internal-root-domain' // not inside the blog or subdomain, but inside the root domain
	| 'external' // outside the root domain
	| 'mail' // mailto:
	| 'tel' // tel:
	| 'anchor' // #anchor
	| 'other'; // anything else

export interface Link {
	index: number;
	type: LinkType;
	originalHref: string;
	href: string;
	anchor: string | null;

	pos: number;
}

function isValidUrlAnyProtocol(url: string) {
	try {
		new URL(url);
		return true;
	} catch (e) {
		return false;
	}
}

export function getLinksFromContent(content: string | null, baseUrl: string): Link[] {
	const doc = getDocFromContent(content);
	const links: Link[] = [];

	doc.descendants((node, pos) => {
		const marks = node.marks;
		if (marks.length === 0) return;

		node.marks.forEach((mark, i) => {
			if (mark.type.name !== 'link') return;
			const href = mark.attrs.href;
			if (!href) return;

			const type = getLinkType(href, baseUrl);
			if (!type) return;

			links.push({
				index: links.length,
				type,
				originalHref: href,
				href: isValidUrlAnyProtocol(href)
					? href // absolute url
					: getFullUrl(href, baseUrl)?.toString() || href,
				anchor: node.textContent ? node.textContent : null,

				pos
			});
		});
	});

	return links;
}

export function getLinkType(href: string, baseUrl: string): LinkType {
	if (href.startsWith('mailto:')) return 'mail';
	if (href.startsWith('tel:')) return 'tel';
	if (href.startsWith('#')) return 'anchor';

	// const baseUrl = blogUrl.endsWith('/') ? blogUrl : blogUrl + '/';
	const hrefFullUrl = getFullUrl(href, baseUrl);
	if (!hrefFullUrl) return 'other';

	if (hrefFullUrl.toString().startsWith(baseUrl)) return 'internal-blog';
	if (hrefFullUrl.protocol !== 'https:' && hrefFullUrl.protocol !== 'http:') return 'other';

	const { domain: blogDomain, subdomain: blogSubdomain } = parse(baseUrl);
	const { domain: hrefDomain, subdomain: hrefSubdomain } = parse(hrefFullUrl.toString());

	if (blogDomain === hrefDomain) {
		return blogSubdomain === hrefSubdomain ? 'internal-domain' : 'internal-root-domain';
	} else {
		return 'external';
	}
}

export function focusLinkInEditor(link: Link, editorView: EditorView | null) {
	if (!editorView) return;

	const doc = editorView.state.doc;
	const pos = link.pos;

	const resolvedPos = doc.resolve(pos);
	const selection = TextSelection.create(doc, pos, pos + resolvedPos.nodeAfter!.nodeSize);

	editorView.dispatch(editorView.state.tr.setSelection(selection).scrollIntoView());
	editorView.focus();

	positionSelectionInMiddleOfScreen(editorView);
}

export function getFullUrl(href: string, baseUrl: string): URL | null {
	// const baseUrl = blogUrl.endsWith('/') ? blogUrl : blogUrl + '/';
	try {
		return new URL(href, baseUrl);
	} catch (e) {
		return null;
	}
}

export function calculateLinkAnalysis(variant: PostVariant): Record<string, number> {
	const content = variant.content_unsaved || variant.content;
	const links = getLinksFromContent(content, variant.url);

	const linkStatuses: Record<string, number> = {};
	const linkAnalysis = variant.link_analysis || {};

	links.forEach((link, i) => {
		let status = linkAnalysis[link.originalHref] || LINK_STATUS.LOADING;

		if (link.type === 'anchor') {
			const doc = getDocFromContent(content);
			const anchorId = link.originalHref.replace('#', '').trim();

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

		// ignore links longer than 255 characters
		if (link.originalHref.length > 255) {
			status = LINK_STATUS.IGNORED;
		}

		linkStatuses[link.originalHref] = status;
	});

	return linkStatuses;
}

export function isHttpLink(link: Link) {
	return (
		link.type === 'internal-blog' ||
		link.type === 'internal-domain' ||
		link.type === 'internal-root-domain' ||
		link.type === 'external'
	);
}

export function getStatusType(
	status: number
): 'loading' | 'ok' | 'redirect' | 'broken' | 'ignored' | 'error' {
	if (status === LINK_STATUS.LOADING) return 'loading';
	if (status === LINK_STATUS.IGNORED) return 'ignored';
	if (status === LINK_STATUS.ERROR) return 'error';
	if (status >= 200 && status < 300) return 'ok';
	if (status >= 300 && status < 400) return 'redirect';
	return 'broken';
}

export function getCountsByStatus(statuses: Record<string, number>) {
	const counts = {
		total: 0,
		ok: 0,
		redirect: 0,
		broken: 0,
		ignored: 0,
		loading: 0
	};

	for (const link in statuses) {
		const status = statuses[link]!;
		let statusType = getStatusType(status);
		if (statusType === 'error') statusType = 'broken';
		counts.total++;
		counts[statusType]++;
	}

	return counts;
}


/* 
export function useUpdateLinkAnalysis(id: number) {

    const { updateCurrentPostVariantValue } = usePostActions(id);
    const { currentVariant, currentVariantLinkAnalysis, currentVariantLinks } = usePostValues(id);

    const links = currentVariantLinks;
    let linksCount = links.length;

    let okCount = 0;
    let redirectCount = 0;
    let brokenCount = 0;
    let ignoreCount = 0;
    let loadingCount = 0;

    links.forEach(link => {
        const status = currentVariantLinkAnalysis[link.originalHref];
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

    function getResultObjectFromLinks(links: LinkAnalysisLink[]) {
        const obj : Record<string, number> = {}
        links.forEach(link => {
            obj[link.url] = link.ignored ? LINK_STATUS.IGNORED : link.status_code;
        })
        return obj;
    }

    useEffect(() => {

        const loadingLinks : string[] = [];

        for (const link in currentVariantLinkAnalysis) {
            if (currentVariantLinkAnalysis[link] === LINK_STATUS.LOADING) {
                loadingLinks.push(link);
            }
        }

        if (lastLoadedLinksRef.current === loadingLinks.join(',')) return;
        if (loadingLinks.length === 0) return;

        callLinkAnalysisApi(currentVariant.id, loadingLinks)
            .then(res => {
                updateCurrentPostVariantValue('link_analysis', {
                    ...currentVariantLinkAnalysis,
                    ...getResultObjectFromLinks(res),
                })
            })
            .catch(() => {

                updateCurrentPostVariantValue('link_analysis', {
                    ...currentVariantLinkAnalysis,
                    ...loadingLinks.reduce((acc, link) => {
                        acc[link] = LINK_STATUS.ERROR;
                        return acc;
                    }, {} as Record<string, number>)
                });

            });

        lastLoadedLinksRef.current = loadingLinks.join(',');

    }, [currentVariantLinkAnalysis]);

    return {

        reloadLink: (link: Link, onReload: (status: number) => void) => {

            callLinkAnalysisApi(
                currentVariant.id, 
                [link.originalHref], 
                // true
            ).then(res => {

                const status = res.find(l => l.url === link.originalHref)?.status_code || LINK_STATUS.ERROR;

                updateCurrentPostVariantValue('link_analysis', {
                    ...currentVariantLinkAnalysis,
                    [link.originalHref]: status,
                })

                onReload(status);

            }).catch(() => {
                    
                updateCurrentPostVariantValue('link_analysis', {
                    ...currentVariantLinkAnalysis,
                    [link.originalHref]: LINK_STATUS.ERROR,
                })

                onReload(LINK_STATUS.ERROR);
    
            })

        },

        reloadAllLinks: (onReload: Function) => {

            // updateCurrentPostVariantValue('link_analysis', {})
            // return;

            const allLinks = links
                .filter(link => isHttpLink(link))
                .map(link => link.href)

            callLinkAnalysisApi(
                currentVariant.id,
                allLinks,
                // true,
            ).then(res => {  
                updateCurrentPostVariantValue('link_analysis', {
                    ...currentVariantLinkAnalysis,
                    ...getResultObjectFromLinks(res),
                })
            })
            .catch(e => {
                toast.error(e);
            })
            .finally(() => {
                onReload();
            })

        },

        ignoreLink: (link: Link, status: boolean = true) => {
            
            return callIgnoreLink(currentVariant.id, link.href, status)
                .then(res => {
                    updateCurrentPostVariantValue('link_analysis', {
                        ...currentVariantLinkAnalysis,
                        [link.href]: status ? LINK_STATUS.IGNORED : res.status_code
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

export function callLinkAnalysisApi(
    postVariantId: number,
    urls: string[], // originalUrls
    // force: boolean = false
) {

    const subdomain = getSubdomain();

    return api.post<LinkAnalysisLink[]>(subdomain, '/link-analysis/check-urls', {
        post_variant_id: postVariantId,
        urls
    })

}

export function callIgnoreLink(postVariantId: number, url: string, status: boolean) {

    const subdomain = getSubdomain();
    return api.patch<LinkAnalysisLink>(subdomain, '/link-analysis/ignore-link', {
        post_variant_id: postVariantId,
        url,
        status: status ? 1 : 0,
    });

}  */
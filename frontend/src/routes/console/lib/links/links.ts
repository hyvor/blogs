import { EditorView } from 'prosemirror-view';
import { getDocFromContent, positionSelectionInMiddleOfScreen } from '../prosemirror/helpers';
import { TextSelection } from 'prosemirror-state';
import { parse } from 'tldts';
import type { LinkAnalysisStatusType } from '../types';

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

export function calculateLinkAnalysis(
	links: Link[],
	content: string | null,
	linkAnalysis: Record<string, number> | null | undefined
): Record<string, number> {
	const linkStatuses: Record<string, number> = {};
	linkAnalysis = linkAnalysis || {};

	links.forEach((link, i) => {
		let status = linkAnalysis![link.originalHref];

		if (status === undefined) {
			status = LINK_STATUS.LOADING;
		}

		if (link.type === 'anchor') {
			const doc = getDocFromContent(content);
			const anchorId = link.originalHref.replace('#', '').trim();

			let found = false;

			doc.descendants((node) => {
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
			status = LINK_STATUS.IGNORED;
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

export function getStatusType(status: number): 'loading' | 'error' | LinkAnalysisStatusType {
	if (status === LINK_STATUS.LOADING) return 'loading';
	if (status === LINK_STATUS.IGNORED) return 'ignored';
	if (status === LINK_STATUS.ERROR) return 'error';
	if (status >= 200 && status < 300) return 'ok';
	if (status >= 300 && status < 400) return 'redirect';
	if (status === 404 || status === 0) return 'broken';
	return 'risky';
}

export function getCountsByStatus(statuses: Record<string, number>) {
	const counts = {
		total: 0,
		ok: 0,
		redirect: 0,
		risky: 0,
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

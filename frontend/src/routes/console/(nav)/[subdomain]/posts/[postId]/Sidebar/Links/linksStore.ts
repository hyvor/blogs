import { get, writable } from 'svelte/store';
import { postVariantStore, updatePostVariantStore } from '../../../postStore';
import { blogStore } from '../../../../../../lib/stores/blogStore';
import {
	calculateLinkAnalysis,
	getLinksFromContent,
	getStatusType,
	LINK_STATUS,
	type Link
} from '../../../../../../lib/links/links';
import { callLinkAnalysisApi } from '../../../../tools/link-analysis/linkAnalysisActions';
import type { LinkAnalysisLink } from '../../../../../../lib/types';

const RECALCULATE_DEBOUNCE_MS = 1000;

// live links extracted from the post variant's content, kept up to date by linksService
export const variantLinksStore = writable<Link[]>([]);
// live link-check status (URL -> status code / LINK_STATUS), kept up to date by linksService
export const variantLinkAnalysisStore = writable<Record<string, number>>({});
export const variantLinkCountsStore = writable(getLinkCounts({}, []));

/**
 * This saves the full link objects from the API
 */
export const linksStore = writable<LinkAnalysisLink[]>([]);


class LinksService {
	private content: string | null = null;
	private recalculateTimeout: ReturnType<typeof setTimeout> | null = null;
	private unsubscribeVariant: (() => void) | null = null;
	private unsubscribeAnalysis: (() => void) | null = null;
	private lastLoadedLinks: string | null = null;

	start(initialContent: string | null = null) {
		this.stop();

		this.content = initialContent;
		this.unsubscribeVariant = postVariantStore.subscribe(() => this.scheduleRecalculate());
		this.unsubscribeAnalysis = variantLinkAnalysisStore.subscribe((analysis) =>
			this.loadPendingLinks(analysis)
		);
	}

	stop() {
		this.unsubscribeVariant?.();
		this.unsubscribeVariant = null;

		this.unsubscribeAnalysis?.();
		this.unsubscribeAnalysis = null;

		if (this.recalculateTimeout) clearTimeout(this.recalculateTimeout);
		this.recalculateTimeout = null;

		this.content = null;
		this.lastLoadedLinks = null;
		variantLinksStore.set([]);
		variantLinkAnalysisStore.set({});
		variantLinkCountsStore.set(getLinkCounts({}, []));
	}

	// called by the editor (Editor.svelte's onvaluechange) whenever the live document changes
	updateContent(content: string) {
		this.content = content;
		this.scheduleRecalculate();
	}

	private scheduleRecalculate() {
		if (this.recalculateTimeout) clearTimeout(this.recalculateTimeout);
		this.recalculateTimeout = setTimeout(() => this.recalculate(), RECALCULATE_DEBOUNCE_MS);
	}

	private recalculate() {
		const variant = get(postVariantStore);
		if (!variant) return;

		const blog = get(blogStore);
		const links = getLinksFromContent(this.content, blog.url);
		const analysis = calculateLinkAnalysis(links, this.content, variant.link_analysis);

		variantLinksStore.set(links);
		variantLinkAnalysisStore.set(analysis);
		variantLinkCountsStore.set(getLinkCounts(analysis, links));
	}

	private loadPendingLinks(analysis: Record<string, number>) {
		const loadingLinks = Object.keys(analysis).filter(
			(link) => analysis[link] === LINK_STATUS.LOADING
		);

		if (loadingLinks.length === 0) return;

		if (this.lastLoadedLinks === loadingLinks.join(',')) return;
		this.lastLoadedLinks = loadingLinks.join(',');

		const variant = get(postVariantStore);
		if (!variant) return;

		callLinkAnalysisApi(variant.id, loadingLinks)
			.then((res) => {
				updatePostVariantStore({
					link_analysis: {
						...analysis,
						...getResultObjectFromLinks(res)
					}
				});

				linksStore.update((links) => links.concat(res));
			})
			.catch(() => {
				updatePostVariantStore({
					link_analysis: {
						...analysis,
						...loadingLinks.reduce(
							(acc, link) => {
								acc[link] = LINK_STATUS.ERROR;
								return acc;
							},
							{} as Record<string, number>
						)
					}
				});
			});
	}
}

export const linksService = new LinksService();

export function getResultObjectFromLinks(links: LinkAnalysisLink[]) {
	const obj: Record<string, number> = {};
	links.forEach((link) => {
		obj[link.url] = link.ignored ? LINK_STATUS.IGNORED : link.status_code;
	});
	return obj;
}

function getLinkCounts(analysis: Record<string, number>, links: Link[]) {
	let okCount = 0;
	let redirectCount = 0;
	let brokenCount = 0;
	let riskyCount = 0;
	let ignoreCount = 0;
	let loadingCount = 0;

	links.forEach((link) => {
		let status = analysis[link.originalHref];
		if (status === undefined) {
			status = LINK_STATUS.ERROR;
		}

		const statusType = getStatusType(status);

		if (statusType === 'ok') {
			okCount++;
		} else if (statusType === 'redirect') {
			redirectCount++;
		} else if (statusType === 'broken') {
			brokenCount++;
		} else if (statusType === 'risky') {
			riskyCount++;
		} else if (statusType === 'ignored') {
			ignoreCount++;
		} else if (statusType === 'loading') {
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
		loading: loadingCount
	};
}

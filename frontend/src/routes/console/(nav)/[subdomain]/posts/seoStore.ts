import { get, writable } from 'svelte/store';
import { postVariantStore } from './postStore';
import { updatePostVariant } from './postActions';
import { type Output as SeoOutput, SeoAnalyzer } from '../../../lib/seo/seo-analyzer';
import type { Blog, Language, PostVariant } from '../../../lib/types';
import { blogStore } from '../../../lib/stores/blogStore';
import { getLanguageById } from '../../../lib/actions/languageActions';

const EMPTY_OUTPUT: SeoOutput = { average: 0, tests: [] };

const RECALCULATE_DEBOUNCE_MS = 1000;
const SAVE_INTERVAL_MS = 3000;

// live SEO analysis for the post variant currently being edited, kept up to date by seoService
export const variantSeoStore = writable<SeoOutput>(EMPTY_OUTPUT);

export function analyzePostVariant(
	variant: PostVariant,
	content: string | null,
	blog: Blog | null = null,
	language: Language | null = null
): SeoOutput {
	blog = blog || get(blogStore);
	language = language || getLanguageById(variant.language_id) || null;

	if (!blog || !language) return EMPTY_OUTPUT;

	const analyzer = new SeoAnalyzer({
		primaryKeyword: variant.seo_primary_keyword,
		secondaryKeywords: variant.seo_secondary_keywords,
		title: variant.title || '',
		slug: variant.slug || '',
		description: variant.description || '',
		content,
		blogUrl: blog.url,
		languageCode: language.code
	});

	return analyzer.analyze();
}

class SeoService {
	private content: string | null = null;
	private lastSavedScore: number | null = null;
	private recalculateTimeout: ReturnType<typeof setTimeout> | null = null;
	private saveInterval: ReturnType<typeof setInterval> | null = null;
	private unsubscribeVariant: (() => void) | null = null;

	start(initialContent: string | null = null) {
		this.stop();

		this.content = initialContent;
		this.lastSavedScore = get(postVariantStore)?.seo_score ?? null;

		variantSeoStore.set({ average: this.lastSavedScore ?? 0, tests: [] });

		this.unsubscribeVariant = postVariantStore.subscribe(() => this.scheduleRecalculate());
		this.saveInterval = setInterval(() => this.saveIfChanged(), SAVE_INTERVAL_MS);
	}

	stop() {
		this.unsubscribeVariant?.();
		this.unsubscribeVariant = null;

		if (this.recalculateTimeout) clearTimeout(this.recalculateTimeout);
		this.recalculateTimeout = null;

		if (this.saveInterval) clearInterval(this.saveInterval);
		this.saveInterval = null;

		this.content = null;
		this.lastSavedScore = null;
		variantSeoStore.set(EMPTY_OUTPUT);
	}

	// called by the editor (Editor.svelte's onvaluechange) when the live document changes
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

		variantSeoStore.set(analyzePostVariant(variant, this.content));
	}

	private saveIfChanged() {
		const variant = get(postVariantStore);
		if (!variant) return;

		const score = Math.round(get(variantSeoStore).average);
		if (score === this.lastSavedScore) return;

		this.lastSavedScore = score;
		updatePostVariant({ seo_score: score }, false).catch((e) => {
			console.error('Failed to save SEO score', e);
		});
	}
}

export const seoService = new SeoService();

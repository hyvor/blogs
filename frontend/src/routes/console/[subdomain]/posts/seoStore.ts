import { derived, get } from "svelte/store";
import { postLanguageStore, postVariantStore } from "./postStore";
import { type Output as SeoOutput, SeoAnalyzer } from "../../lib/seo/seo-analyzer";
import type { Blog, Language, PostVariant } from "../../lib/types";
import { blogStore } from "../../lib/stores/blogStore";
import { getLanguageById } from "../../lib/actions/languageActions";

let seoTimeout : null | ReturnType<typeof setTimeout> = null;

export const variantSeoStore = derived(
    [postVariantStore, blogStore, postLanguageStore],
    ([variant, blog, language], set) => {
        if (seoTimeout) {
            clearTimeout(seoTimeout);
        }
        seoTimeout = setTimeout(() => {
            set(analyzePostVariant(variant, blog, language));
        }, 100);
    },
    {average: 0, tests: []} as SeoOutput
)

export function analyzePostVariant(
    variant: PostVariant, 
    blog: Blog | null = null,
    language: Language | null = null
) {

    blog = blog || get(blogStore);
    language = language || getLanguageById(variant.language_id)!;

    function getSeoResultsInput(variant: PostVariant) {
        return {
            primaryKeyword: variant.seo_primary_keyword,
            secondaryKeywords: variant.seo_secondary_keywords,
            title: variant.title || '',
            slug: variant.slug || '',
            description: variant.description || '',
            content: variant.content_unsaved || variant.content
        };
    }

    const analyzer = new SeoAnalyzer({
        ...getSeoResultsInput(variant),
        blogUrl: blog.url,
        languageCode: language.code
    })
    const results = analyzer.analyze();

    return results;
}
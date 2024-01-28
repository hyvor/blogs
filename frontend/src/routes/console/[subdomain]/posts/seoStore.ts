import { derived, get } from "svelte/store";
import { postLanguageStore, postVariantStore } from "./postStore";
import { SeoAnalyzer } from "../../lib/seo/seo-analyzer";
import type { Blog, Language, PostVariant } from "../../lib/types";
import { blogStore } from "../../lib/stores/blogStore";
import { getLanguageById } from "../../lib/actions/languageActions";

export const variantSeoStore = derived(
    [postVariantStore, blogStore, postLanguageStore],
    ([variant, blog, language]) => analyzePostVariant(variant, blog, language)
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
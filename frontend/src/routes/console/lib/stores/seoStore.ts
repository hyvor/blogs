import { derived } from "svelte/store";
import { postLanguageStore, postVariantStore } from "./postStore";
import { SeoAnalyzer } from "../seo/seo-analyzer";
import type { PostVariant } from "../types";
import { blogStore } from "./blogStore";

export const variantSeoStore = derived(
    [postVariantStore, blogStore, postLanguageStore],
    ([variant, blog, language]) => {

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
)
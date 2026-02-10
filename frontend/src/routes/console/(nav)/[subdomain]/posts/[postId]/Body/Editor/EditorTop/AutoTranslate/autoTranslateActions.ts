import consoleApi from '../../../../../../../../lib/consoleApi';

export function autoTranslate(
	sourceLanguage: string,
	targetLanguage: string,
	content: string | null,
	title: string | null,
	description: string | null,
	slug: string | null
) {
	/* 
    title: post.variants[0].title,
            description: post.variants[0].description,
            slug: post.variants[0].slug,
            content: post.variants[0].content,
            source_lang: sourceLanguage,
            target_lang: targetLanguage */

	return consoleApi.post<{ title: string; description: string; slug: string; content: string }>({
		endpoint: '/ai/translate',
		data: {
			title,
			description,
			slug,
			content,
			source_lang: sourceLanguage,
			target_lang: targetLanguage
		}
	});
}

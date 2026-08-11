import consoleApi from '../../../../../../../../lib/consoleApi';

export function autoTranslate(
	postVariantId: number,
	targetLanguage: string,
) {
	return consoleApi.post<{
		title: string;
		description: string;
		slug: string;
		content: string;
	}>({
		endpoint: '/ai/translate/post',
		data: {
			post_variant_id: postVariantId,
			target_language_code: targetLanguage,
		}
	});
}

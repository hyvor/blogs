import consoleApi from "../../../../lib/consoleApi";
import type { Document, Post, PostVariant } from "../../../../lib/types";


export function getDocumentForPost(id: number, variantLanguageCode: string | null = null) {
	return consoleApi.get<{
		post: Post;
		variant: PostVariant;
		document: Document;
	}>({
		endpoint: `/documents/post`,
		data: {
            post_id: id,
            variant_language_code: variantLanguageCode
        }
	});
}
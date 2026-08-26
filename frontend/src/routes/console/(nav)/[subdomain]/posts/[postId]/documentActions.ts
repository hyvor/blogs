import consoleApi from "../../../../lib/consoleApi";
import type { Document, Post, PostVariant } from "../../../../lib/types";
import type { CollabStep } from "./Body/Editor/collab";

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

export interface CheckpointClientBehindError {
	message: 'client_behind';
	version: number;
	steps: CollabStep[];
}

export interface CheckpointClientAheadError {
	message: 'client_ahead';
	message_full: string;
}


export function saveCheckpoint(data: {
	post_variant_id: number;
	version: number;
	content: string;
}) {
	return consoleApi.post<void>({
		endpoint: `/documents/checkpoint`,
		data
	});
}

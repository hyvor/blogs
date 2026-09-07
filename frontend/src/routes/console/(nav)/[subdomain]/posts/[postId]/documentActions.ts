import consoleApi from '../../../../lib/consoleApi';
import type { Document, Post, PostVariant } from '../../../../lib/types';
import type { CollabStep } from './Body/Editor/collab';

export function getDocumentForPost(params: {
	post_id?: number;
	variant_language_code?: string | null;
	post_variant_id?: number;
}) {
	return consoleApi.get<{
		post: Post;
		variant: PostVariant;
		document: Document;
	}>({
		endpoint: `/documents/post`,
		data: params
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

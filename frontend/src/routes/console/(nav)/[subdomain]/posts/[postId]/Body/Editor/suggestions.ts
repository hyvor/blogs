import type {
	Author,
	AuthorInfo,
	SuggestionSource,
	SuggestionSourceEntry,
	SuggestionSubtype
} from '@hyvor/richtext';
import {
	createPostSuggestion,
	deletePostSuggestionReply,
	editPostSuggestionReply,
	getPostSuggestions,
	replyToPostSuggestion,
	resolvePostSuggestion,
	resolveSuggestionAuthor
} from '../../../postSuggestionActions';

// local, in-memory id -> type cache, filled in by `create` before `reply` could ever
// need it. @hyvor/richtext's SuggestionSource.reply(id, reply) has no `type` of its
// own (see plugin-suggestions.ts), but the backend's reply endpoint accepts an
// optional `type` to auto-create the parent suggestion if its own `create` call
// hasn't landed yet (create/reply are independent fire-and-forget calls that can
// arrive out of order - see PostSuggestionService::reply() on the backend).
const typeCache = new Map<string, SuggestionSubtype>();

export const suggestionSource: SuggestionSource = {
	async get(ids) {
		const result = await getPostSuggestions(ids);
		const entries: Record<string, SuggestionSourceEntry | null> = {};
		for (const id of ids) {
			const entry = result[id];
			entries[id] = entry
				? {
						author: entry.author as Author,
						timestamp: entry.timestamp,
						comments: entry.comments.map((reply) => ({
							...reply,
							author: reply.author as Author
						}))
					}
				: null;
		}
		return entries;
	},
	create(id, type, _author, _timestamp) {
		typeCache.set(id, type);
		createPostSuggestion(id, type).catch((e) => console.error('Failed to sync suggestion', e));
	},
	reply(id, reply) {
		replyToPostSuggestion(id, reply.id, reply.content, typeCache.get(id)).catch((e) =>
			console.error('Failed to sync suggestion reply', e)
		);
	},
	editReply(id, replyId, content) {
		editPostSuggestionReply(id, replyId, content).catch((e) =>
			console.error('Failed to sync suggestion reply edit', e)
		);
	},
	deleteReply(id, replyId) {
		deletePostSuggestionReply(id, replyId).catch((e) =>
			console.error('Failed to sync suggestion reply delete', e)
		);
	},
	resolve(id, decision) {
		typeCache.delete(id);
		resolvePostSuggestion(id, decision).catch((e) =>
			console.error('Failed to resolve suggestion', e)
		);
	}
};

const authorCache = new Map<Author, AuthorInfo>();

export async function resolveAuthor(author: Author): Promise<AuthorInfo> {
	if (author === 'ai') {
		return { name: 'AI', picture: ROBOT_PICTURE };
	}

	const cached = authorCache.get(author);
	if (cached) return cached;

	const hyvorUserId = parseInt(author.slice('user:'.length), 10);
	const data = await resolveSuggestionAuthor(hyvorUserId);
	const info: AuthorInfo = {
		name: data.name ?? 'Unknown',
		picture: data.picture_url ?? undefined
	};

	authorCache.set(author, info);
	return info;
}

const ROBOT_PICTURE =
	'data:image/svg+xml,%3Csvg xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 width%3D%2228%22 height%3D%2228%22%3E%3Ccircle cx%3D%2220%22 cy%3D%2220%22 r%3D%2250%22 fill%3D%22%23e3cecd%22 %2F%3E%3Cpath transform%3D%22translate(6 6)%22 d%3D%22M6 12.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5M3 8.062C3 6.76 4.235 5.765 5.53 5.886a26.6 26.6 0 0 0 4.94 0C11.765 5.765 13 6.76 13 8.062v1.157a.93.93 0 0 1-.765.935c-.845.147-2.34.346-4.235.346s-3.39-.2-4.235-.346A.93.93 0 0 1 3 9.219zm4.542-.827a.25.25 0 0 0-.217.068l-.92.9a25 25 0 0 1-1.871-.183.25.25 0 0 0-.068.495c.55.076 1.232.149 2.02.193a.25.25 0 0 0 .189-.071l.754-.736.847 1.71a.25.25 0 0 0 .404.062l.932-.97a25 25 0 0 0 1.922-.188.25.25 0 0 0-.068-.495c-.538.074-1.207.145-1.98.189a.25.25 0 0 0-.166.076l-.754.785-.842-1.7a.25.25 0 0 0-.182-.135%22 %2F%3E%3Cpath transform%3D%22translate(6 6)%22 d%3D%22M8.5 1.866a1 1 0 1 0-1 0V3h-2A4.5 4.5 0 0 0 1 7.5V8a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1v1a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-1a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1v-.5A4.5 4.5 0 0 0 10.5 3h-2zM14 7.5V13a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.5A3.5 3.5 0 0 1 5.5 4h5A3.5 3.5 0 0 1 14 7.5%22%2F%3E%3C%2Fsvg%3E';

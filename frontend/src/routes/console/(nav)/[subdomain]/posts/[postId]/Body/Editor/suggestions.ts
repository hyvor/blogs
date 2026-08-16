import type {
	Author,
	AuthorInfo,
	SuggestionSource,
	SuggestionSourceEntry,
	SuggestionSubtype
} from '@hyvor/richtext';
import {
	createPostSuggestion,
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
						comments: entry.comments.map((reply) => ({
							...reply,
							author: reply.author as Author
						}))
					}
				: null;
		}
		return entries;
	},
	create(id, type, _author) {
		typeCache.set(id, type);
		createPostSuggestion(id, type).catch((e) => console.error('Failed to sync suggestion', e));
	},
	reply(id, reply) {
		replyToPostSuggestion(id, reply.id, reply.content, typeCache.get(id)).catch((e) =>
			console.error('Failed to sync suggestion reply', e)
		);
	},
	resolve(id, decision) {
		typeCache.delete(id);
		resolvePostSuggestion(id, decision).catch((e) => console.error('Failed to resolve suggestion', e));
	}
};

const authorCache = new Map<Author, AuthorInfo>();

export async function resolveAuthor(author: Author): Promise<AuthorInfo> {
	if (author === 'ai') {
		return { name: 'AI' };
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

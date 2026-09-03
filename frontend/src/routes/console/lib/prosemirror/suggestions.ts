type PmNode = {
	attrs?: Record<string, unknown> | null;
	marks?: Array<Record<string, unknown>> | null;
	content?: unknown;
};

function nonCommentSuggestion(value: unknown): boolean {
	return (
		!!value && typeof value === 'object' && (value as Record<string, unknown>).type !== 'comment'
	);
}

function nodeHasPendingSuggestions(node: PmNode): boolean {
	const suggestions = node.attrs?.suggestions;
	if (Array.isArray(suggestions) && suggestions.some(nonCommentSuggestion)) {
		return true;
	}

	if (Array.isArray(node.marks)) {
		for (const mark of node.marks) {
			if (
				mark &&
				typeof mark === 'object' &&
				mark.type === 'suggestion' &&
				mark.attrs &&
				typeof mark.attrs === 'object' &&
				(mark.attrs as Record<string, unknown>).type !== 'comment'
			) {
				return true;
			}
		}
	}

	if (Array.isArray(node.content)) {
		for (const child of node.content) {
			if (child && typeof child === 'object' && nodeHasPendingSuggestions(child as PmNode)) {
				return true;
			}
		}
	}

	return false;
}

/**
 * @param content ProseMirror document as a JSON string (or already-parsed object)
 */
export function hasPendingSuggestions(content: string | object | null): boolean {
	if (!content) return false;

	let decoded: unknown;
	if (typeof content === 'string') {
		if (content === '') return false;
		try {
			decoded = JSON.parse(content);
		} catch {
			return false;
		}
	} else {
		decoded = content;
	}

	if (!decoded || typeof decoded !== 'object') return false;

	return nodeHasPendingSuggestions(decoded as PmNode);
}

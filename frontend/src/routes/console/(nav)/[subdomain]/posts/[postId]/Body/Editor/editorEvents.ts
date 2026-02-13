import type { DOMEventMap, EditorView } from 'prosemirror-view';
import { get, writable } from 'svelte/store';

export type ProsemirrorEvent<T extends keyof DOMEventMap = keyof DOMEventMap> = DOMEventMap[T];

export type ProsemirrorEventDispatchType<T extends keyof DOMEventMap = keyof DOMEventMap> = {
	view: EditorView;
	name: T;
	event: DOMEventMap[T];
};

type StoreType<T extends keyof DOMEventMap = keyof DOMEventMap> = Partial<
	Record<T, ((event: DOMEventMap[T]) => void)[]>
>;

export const editorEventHandlers = writable<StoreType>({});

export function initEditorEventHandlers() {
	editorEventHandlers.set({});
}

export function addEditorEventListener<T extends keyof DOMEventMap>(
	key: T,
	handler: (e: DOMEventMap[T]) => void
) {
	editorEventHandlers.update((events) => {
		if (!events[key]) {
			events[key] = [];
		}

		events[key]!.push(handler);

		return events;
	});
}

export function handleEditorEventHandlers<T extends keyof DOMEventMap>(
	key: T,
	event: DOMEventMap[T]
) {
	const handlers = get(editorEventHandlers)[key] || [];
	handlers.forEach((handler) => handler(event));
}

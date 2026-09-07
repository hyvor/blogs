declare module '*.md' {
	import type { Component } from 'svelte';
	const component: Component;
	export default component;
}

declare type Item = import('svelte-dnd-action').Item;
declare type DndEvent<ItemType = Item> = import('svelte-dnd-action').DndEvent<ItemType>;
declare namespace svelteHTML {
	interface HTMLAttributes<T> {
		'on:consider'?: (event: CustomEvent<DndEvent<Item>> & { target: EventTarget & T }) => void;
		'on:finalize'?: (event: CustomEvent<DndEvent<Item>> & { target: EventTarget & T }) => void;
	}
}

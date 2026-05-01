import type { Component } from 'svelte';
import Introduction from './content/Introduction.md';
import Deploy from './content/Deploy.md';

export const categories: Category[] = [
	{
		name: 'Hosting',
		pages: [
			{
				slug: '',
				name: 'Introduction',
				component: Introduction as unknown as Component
			},
			{
				slug: 'deploy',
				name: 'Deploy',
				component: Deploy as unknown as Component
			}
		]
	}
];

export const pages = categories.reduce((acc, category) => acc.concat(category.pages), [] as Page[]);

interface Category {
	name: string;
	pages: Page[];
}

interface Page {
	slug: string;
	name: string;
	component: Component;
}

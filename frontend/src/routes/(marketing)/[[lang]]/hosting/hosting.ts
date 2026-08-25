import type { NavSectionConfig } from '@hyvor/design/marketing';
import Introduction from './content/Introduction.md';
import Deploy from './content/Deploy.svelte';

export const sections: NavSectionConfig[] = [
	{
		name: '',
		navs: [
			{
				type: 'page',
				slug: '',
				name: 'Introduction',
				content: Introduction
			},
			{
				type: 'page',
				slug: 'deploy',
				name: 'Deploy',
				content: Deploy
			}
		]
	}
];

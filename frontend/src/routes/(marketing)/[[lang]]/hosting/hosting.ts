import type { NavSectionConfig } from '@hyvor/design/marketing';
import Introduction from './content/Introduction.md';
import Deploy from './content/Deploy.md';
import Env from './content/Env.md';
import DeliveryDomain from './content/DeliveryDomain.md';

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
	},
	{
		name: 'Configuration',
		navs: [
			{
				type: 'page',
				slug: 'env',
				name: 'Environment Variables',
				content: Env
			},
			{
				type: 'page',
				slug: 'delivery-domain',
				name: 'Delivery Domain',
				content: DeliveryDomain
			}
		]
	}
];

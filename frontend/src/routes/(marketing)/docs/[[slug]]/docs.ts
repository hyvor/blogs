import Introduction from './content/Introduction.svelte';
import Writing from './content/writing/Writing.svelte';
import CustomDomain from './content/custom-domain/CustomDomain.svelte';
import SubDirectoryHosting from './content/subdirectory/SubDirectoryHosting.svelte';
import Services from './content/services/Services.svelte';
import Seo from './content/seo/Seo.svelte';
import Fonts from './content/fonts/Fonts.svelte';
import Redirect from './content/redirect/Redirect.svelte';
import Media from './content/media/Media.svelte';
import Routes from './content/routes/Routes.svelte';
import CustomCode from './content/custom-code/CustomCode.svelte';
import Overview from './content/overview/Overview.svelte';
import ThemeTemplates from './content/themes-templates/ThemesTemplates.svelte';
import ThemesStyles from './content/themes-styles/ThemesStyles.svelte';
import Scripts from './content/scripts/Scripts.svelte';
import Languages from './content/languages/Languages.svelte';
import Export from './content/export/Export.svelte';
import ImportSitemap from './content/import/ImportSitemap.svelte';
import Webhooks from './content/webhooks/Webhooks.svelte';
import ApiDelivery from './content/api-delivery/ApiDelivery.svelte';
import Theme from './content/theme/Theme.svelte';
import Internationalization from './content/internationalization/Internationalization.svelte';
import Configuration from './content/configuration/Configuration.svelte';
import Publishing from './content/publishing/Publishing.svelte';
import ApiConsole from './content/api-console/ApiConsole.svelte';
import ApiData from './content/api-data/ApiData.svelte';
import Headless from './content/headless/Headless.svelte';
import Users from './content/users/Users.svelte';
import SyntaxHighlighting from './content/syntax-highlighting/SyntaxHighlighting.svelte';
import Tags from './content/tags/Tags.svelte';
import NavigationLinks from './content/navigation/NavigationLinks.svelte';
import Import from './content/import/Import.svelte';
import ImportWordPress from './content/import/ImportWordPress.svelte';
import type { Component } from 'svelte';

export const categories: Category[] = [
	{
		name: 'Intro',
		pages: [
			{
				slug: '',
				name: 'Introduction',
				component: Introduction
			},

			{
				slug: 'writing',
				name: 'Writing',
				component: Writing
			},

			{
				slug: 'theme',
				name: 'Theme',
				component: Theme
			}
		]
	},

	{
		name: 'Hosting',
		pages: [
			{
				slug: 'custom-domain',
				name: 'Custom  Domain',
				component: CustomDomain
			},
			{
				slug: 'subdirectory',
				name: 'Subdirectory',
				component: SubDirectoryHosting
			},
			{
				slug: 'headless',
				name: 'Headless',
				component: Headless
			}
		]
	},

	{
		name: 'Features',
		pages: [
			{
				slug: 'languages',
				name: 'Languages',
				component: Languages
			},
			{
				slug: 'users',
				name: 'Users',
				component: Users
			},
			{
				slug: 'tags',
				name: 'Tags',
				component: Tags
			},
			{
				slug: 'media',
				name: 'Media',
				component: Media
			},
			{
				slug: 'fonts',
				name: 'Fonts',
				component: Fonts
			},
			{
				slug: 'seo',
				name: 'SEO',
				component: Seo
			},
			{
				slug: 'navigation-links',
				name: 'Navigation Links',
				component: NavigationLinks
			},
			{
				slug: 'redirects',
				name: 'Redirects',
				component: Redirect
			},
			{
				slug: 'custom-code',
				name: 'Custom Code',
				component: CustomCode
			},
			{
				slug: 'services',
				name: 'Services',
				component: Services
			},
			{
				slug: 'routes',
				name: 'Routes',
				component: Routes
			},
			{
				slug: 'syntax-highlighting',
				name: 'Syntax Highlighting',
				component: SyntaxHighlighting
			}
		]
	},

	{
		name: 'Developer',
		pages: [
			{
				slug: 'webhooks',
				name: 'Webhooks',
				component: Webhooks
			},
			{
				slug: 'api-console',
				name: 'Console API',
				component: ApiConsole
			},
			{
				slug: 'api-delivery',
				name: 'Delivery API',
				component: ApiDelivery
			},
			{
				slug: 'api-data',
				name: 'Data API',
				component: ApiData
			}
		]
	},

	{
		name: 'Data',
		pages: [
			{
				slug: 'export',
				name: 'Export Data',
				component: Export
			},
			{
				slug: 'import',
				name: 'Import Data',
				component: Import
			},
			{
				slug: 'import-sitemap',
				name: 'Sitemap',
				component: ImportSitemap,
				parent: 'import'
			},
			{
				slug: 'import-wordpress',
				name: 'WordPress',
				component: ImportWordPress,
				parent: 'import'
			}
		]
	},

	{
		name: 'Theme Development',
		pages: [
			{
				slug: 'themes-overview',
				name: 'Overview',
				component: Overview
			},

			{
				slug: 'themes-templates',
				name: 'Templates',
				component: ThemeTemplates
			},

			{
				slug: 'themes-styles',
				name: 'Styling',
				component: ThemesStyles
			},

			{
				slug: 'themes-scripts',
				name: 'Scripts',
				component: Scripts
			},

			{
				slug: 'themes-internationalization',
				name: 'Internationalization',
				component: Internationalization
			},

			{
				slug: 'themes-config',
				name: 'Configuration',
				component: Configuration
			},

			{
				slug: 'themes-publishing',
				name: 'Publishing',
				component: Publishing
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
	parent?: string;
}

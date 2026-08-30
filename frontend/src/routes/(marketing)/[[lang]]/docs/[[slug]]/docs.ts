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
import HyvorTalkDoc from './content/hyvor-talk/HyvorTalkDoc.svelte';
import HyvorPostDoc from './content/hyvor-post/HyvorPostDoc.svelte';
import type { NavSectionConfig } from '@hyvor/design/marketing';
import type { Component } from 'svelte';

export async function getSections(lang: string): Promise<NavSectionConfig[]> {

	async function getComponent(path: string): Promise<Component> {
		const folder = lang ? lang.replace('/', '') : 'content';
		return (await import(`./${folder}/${path}.svelte`)).default;
	}

	return	[
	{
		name: '',
		navs: [
			{
				type: 'page',
				slug: '',
				name: 'Introduction',
				content: await getComponent('Introduction'),
			},

			{
				type: 'page',
				slug: 'writing',
				name: 'Writing',
				content: Writing
			},

			{
				type: 'page',
				slug: 'theme',
				name: 'Theme',
				content: Theme
			}
		]
	},

	{
		name: 'Hosting',
		navs: [
			{
				type: 'page',
				slug: 'custom-domain',
				name: 'Custom  Domain',
				content: CustomDomain
			},
			{
				type: 'page',
				slug: 'subdirectory',
				name: 'Subdirectory',
				content: SubDirectoryHosting
			},
			{
				type: 'page',
				slug: 'headless',
				name: 'Headless',
				content: Headless
			}
		]
	},

	{
		name: 'Features',
		navs: [
			{
				type: 'page',
				slug: 'languages',
				name: 'Languages',
				content: Languages
			},
			{
				type: 'page',
				slug: 'users',
				name: 'Users',
				content: Users
			},
			{
				type: 'page',
				slug: 'tags',
				name: 'Tags',
				content: Tags
			},
			{
				type: 'page',
				slug: 'media',
				name: 'Media',
				content: Media
			},
			{
				type: 'page',
				slug: 'fonts',
				name: 'Fonts',
				content: Fonts
			},
			{
				type: 'page',
				slug: 'seo',
				name: 'SEO',
				content: Seo
			},
			{
				type: 'page',
				slug: 'navigation-links',
				name: 'Navigation Links',
				content: NavigationLinks
			},
			{
				type: 'page',
				slug: 'redirects',
				name: 'Redirects',
				content: Redirect
			},
			{
				type: 'page',
				slug: 'custom-code',
				name: 'Custom Code',
				content: CustomCode
			},
			{
				type: 'page',
				slug: 'services',
				name: 'Services',
				content: Services
			},
			{
				type: 'page',
				slug: 'routes',
				name: 'Routes',
				content: Routes
			},
			{
				type: 'page',
				slug: 'syntax-highlighting',
				name: 'Syntax Highlighting',
				content: SyntaxHighlighting
			}
		]
	},

	{
		name: 'Integrations',
		navs: [
			{
				type: 'page',
				slug: 'hyvor-talk',
				name: 'Hyvor Talk',
				content: HyvorTalkDoc
			},
			{
				type: 'page',
				slug: 'hyvor-post',
				name: 'Hyvor Post',
				content: HyvorPostDoc
			}
		]
	},

	{
		name: 'Developer',
		navs: [
			{
				type: 'page',
				slug: 'webhooks',
				name: 'Webhooks',
				content: Webhooks
			},
			{
				type: 'page',
				slug: 'api-console',
				name: 'Console API',
				content: ApiConsole
			},
			{
				type: 'page',
				slug: 'api-delivery',
				name: 'Delivery API',
				content: ApiDelivery
			},
			{
				type: 'page',
				slug: 'api-data',
				name: 'Data API',
				content: ApiData
			}
		]
	},

	{
		name: 'Data',
		navs: [
			{
				type: 'page',
				slug: 'export',
				name: 'Export Data',
				content: Export
			},
			{
				type: 'folding-section',
				name: 'Import Data',
				navs: [
					{
						type: 'page',
						slug: 'import',
						name: 'Overview',
						content: Import
					},
					{
						type: 'page',
						slug: 'import-sitemap',
						name: 'Sitemap',
						content: ImportSitemap
					},
					{
						type: 'page',
						slug: 'import-wordpress',
						name: 'WordPress',
						content: ImportWordPress
					}
				]
			}
		]
	},

	{
		name: 'Theme Development',
		navs: [
			{
				type: 'page',
				slug: 'themes-overview',
				name: 'Overview',
				content: Overview
			},

			{
				type: 'page',
				slug: 'themes-templates',
				name: 'Templates',
				content: ThemeTemplates
			},

			{
				type: 'page',
				slug: 'themes-styles',
				name: 'Styling',
				content: ThemesStyles
			},

			{
				type: 'page',
				slug: 'themes-scripts',
				name: 'Scripts',
				content: Scripts
			},

			{
				type: 'page',
				slug: 'themes-internationalization',
				name: 'Internationalization',
				content: Internationalization
			},

			{
				type: 'page',
				slug: 'themes-config',
				name: 'Configuration',
				content: Configuration
			},

			{
				type: 'page',
				slug: 'themes-publishing',
				name: 'Publishing',
				content: Publishing
			}
		]
	}
];

}

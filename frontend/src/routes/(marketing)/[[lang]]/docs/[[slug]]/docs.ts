import Introduction from './en/Introduction.svelte';
import Writing from './en/writing/Writing.svelte';
import CustomDomain from './en/custom-domain/CustomDomain.svelte';
import SubDirectoryHosting from './en/subdirectory/SubDirectoryHosting.svelte';
import Services from './en/services/Services.svelte';
import Seo from './en/seo/Seo.svelte';
import Fonts from './en/fonts/Fonts.svelte';
import Redirect from './en/redirect/Redirect.svelte';
import Media from './en/media/Media.svelte';
import Routes from './en/routes/Routes.svelte';
import CustomCode from './en/custom-code/CustomCode.svelte';
import Overview from './en/overview/Overview.svelte';
import ThemeTemplates from './en/themes-templates/ThemesTemplates.svelte';
import ThemesStyles from './en/themes-styles/ThemesStyles.svelte';
import Scripts from './en/scripts/Scripts.svelte';
import Languages from './en/languages/Languages.svelte';
import Export from './en/export/Export.svelte';
import ImportSitemap from './en/import/ImportSitemap.svelte';
import Webhooks from './en/webhooks/Webhooks.svelte';
import ApiDelivery from './en/api-delivery/ApiDelivery.svelte';
import Theme from './en/theme/Theme.svelte';
import Internationalization from './en/internationalization/Internationalization.svelte';
import Configuration from './en/configuration/Configuration.svelte';
import Publishing from './en/publishing/Publishing.svelte';
import ApiConsole from './en/api-console/ApiConsole.svelte';
import ApiData from './en/api-data/ApiData.svelte';
import Headless from './en/headless/Headless.svelte';
import Users from './en/users/Users.svelte';
import SyntaxHighlighting from './en/syntax-highlighting/SyntaxHighlighting.svelte';
import Tags from './en/tags/Tags.svelte';
import NavigationLinks from './en/navigation/NavigationLinks.svelte';
import Import from './en/import/Import.svelte';
import ImportWordPress from './en/import/ImportWordPress.svelte';
import HyvorTalkDoc from './en/hyvor-talk/HyvorTalkDoc.svelte';
import HyvorPostDoc from './en/hyvor-post/HyvorPostDoc.svelte';
import type { NavSectionConfig } from '@hyvor/design/marketing';

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

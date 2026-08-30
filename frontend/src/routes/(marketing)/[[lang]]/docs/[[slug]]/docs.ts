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
import { buildI18n, DEFAULT_MARKETING_LANGUAGE } from '../../marketingLang';

export async function getSections(lang: string): Promise<NavSectionConfig[]> {

	async function getComponent(path: string): Promise<Component> {
		const folder = lang ? lang.replace('/', '') : 'content';
		return (await import(`./${folder}/${path}.svelte`)).default;
	}

	const langCode = lang ? lang.replace('/', '') : DEFAULT_MARKETING_LANGUAGE;
	const i18n = buildI18n(langCode);
	const t = i18n.t.bind(i18n);

	return	[
	{
		name: '',
		navs: [
			{
				type: 'page',
				slug: '',
				name: t('docs.pages.introduction'),
				content: await getComponent('Introduction'),
			},

			{
				type: 'page',
				slug: 'writing',
				name: t('docs.pages.writing'),
				content: Writing
			},

			{
				type: 'page',
				slug: 'theme',
				name: t('docs.pages.theme'),
				content: Theme
			}
		]
	},

	{
		name: t('docs.sections.hosting'),
		navs: [
			{
				type: 'page',
				slug: 'custom-domain',
				name: t('docs.pages.customDomain'),
				content: CustomDomain
			},
			{
				type: 'page',
				slug: 'subdirectory',
				name: t('docs.pages.subdirectory'),
				content: SubDirectoryHosting
			},
			{
				type: 'page',
				slug: 'headless',
				name: t('docs.pages.headless'),
				content: Headless
			}
		]
	},

	{
		name: t('docs.sections.features'),
		navs: [
			{
				type: 'page',
				slug: 'languages',
				name: t('docs.pages.languages'),
				content: Languages
			},
			{
				type: 'page',
				slug: 'users',
				name: t('docs.pages.users'),
				content: Users
			},
			{
				type: 'page',
				slug: 'tags',
				name: t('docs.pages.tags'),
				content: Tags
			},
			{
				type: 'page',
				slug: 'media',
				name: t('docs.pages.media'),
				content: Media
			},
			{
				type: 'page',
				slug: 'fonts',
				name: t('docs.pages.fonts'),
				content: Fonts
			},
			{
				type: 'page',
				slug: 'seo',
				name: t('docs.pages.seo'),
				content: Seo
			},
			{
				type: 'page',
				slug: 'navigation-links',
				name: t('docs.pages.navigationLinks'),
				content: NavigationLinks
			},
			{
				type: 'page',
				slug: 'redirects',
				name: t('docs.pages.redirects'),
				content: Redirect
			},
			{
				type: 'page',
				slug: 'custom-code',
				name: t('docs.pages.customCode'),
				content: CustomCode
			},
			{
				type: 'page',
				slug: 'services',
				name: t('docs.pages.services'),
				content: Services
			},
			{
				type: 'page',
				slug: 'routes',
				name: t('docs.pages.routes'),
				content: Routes
			},
			{
				type: 'page',
				slug: 'syntax-highlighting',
				name: t('docs.pages.syntaxHighlighting'),
				content: SyntaxHighlighting
			}
		]
	},

	{
		name: t('docs.sections.integrations'),
		navs: [
			{
				type: 'page',
				slug: 'hyvor-talk',
				name: t('docs.pages.hyvorTalk'),
				content: HyvorTalkDoc
			},
			{
				type: 'page',
				slug: 'hyvor-post',
				name: t('docs.pages.hyvorPost'),
				content: HyvorPostDoc
			}
		]
	},

	{
		name: t('docs.sections.developer'),
		navs: [
			{
				type: 'page',
				slug: 'webhooks',
				name: t('docs.pages.webhooks'),
				content: Webhooks
			},
			{
				type: 'page',
				slug: 'api-console',
				name: t('docs.pages.apiConsole'),
				content: ApiConsole
			},
			{
				type: 'page',
				slug: 'api-delivery',
				name: t('docs.pages.apiDelivery'),
				content: ApiDelivery
			},
			{
				type: 'page',
				slug: 'api-data',
				name: t('docs.pages.apiData'),
				content: ApiData
			}
		]
	},

	{
		name: t('docs.sections.data'),
		navs: [
			{
				type: 'page',
				slug: 'export',
				name: t('docs.pages.export'),
				content: Export
			},
			{
				type: 'folding-section',
				name: t('docs.pages.importData'),
				navs: [
					{
						type: 'page',
						slug: 'import',
						name: t('docs.pages.importOverview'),
						content: Import
					},
					{
						type: 'page',
						slug: 'import-sitemap',
						name: t('docs.pages.importSitemap'),
						content: ImportSitemap
					},
					{
						type: 'page',
						slug: 'import-wordpress',
						name: t('docs.pages.importWordpress'),
						content: ImportWordPress
					}
				]
			}
		]
	},

	{
		name: t('docs.sections.themeDevelopment'),
		navs: [
			{
				type: 'page',
				slug: 'themes-overview',
				name: t('docs.pages.themesOverview'),
				content: Overview
			},

			{
				type: 'page',
				slug: 'themes-templates',
				name: t('docs.pages.themesTemplates'),
				content: ThemeTemplates
			},

			{
				type: 'page',
				slug: 'themes-styles',
				name: t('docs.pages.themesStyling'),
				content: ThemesStyles
			},

			{
				type: 'page',
				slug: 'themes-scripts',
				name: t('docs.pages.themesScripts'),
				content: Scripts
			},

			{
				type: 'page',
				slug: 'themes-internationalization',
				name: t('docs.pages.themesInternationalization'),
				content: Internationalization
			},

			{
				type: 'page',
				slug: 'themes-config',
				name: t('docs.pages.themesConfig'),
				content: Configuration
			},

			{
				type: 'page',
				slug: 'themes-publishing',
				name: t('docs.pages.themesPublishing'),
				content: Publishing
			}
		]
	}
];

}

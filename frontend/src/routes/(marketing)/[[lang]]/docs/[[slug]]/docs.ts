import Writing from './content/Writing.md';
import CustomDomain from './content/CustomDomain.md';
import SubDirectoryHosting from './content/SubDirectoryHosting.md';
import Services from './content/Services.md';
import Seo from './content/Seo.md';
import Fonts from './content/Fonts.md';
import Redirect from './content/Redirect.md';
import Media from './content/Media.md';
import Routes from './content/Routes.md';
import CustomCode from './content/CustomCode.md';
import ThemesOverview from './content/ThemesOverview.md';
import ThemeTemplates from './content/ThemesTemplates.md';
import ThemesStyles from './content/ThemesStyles.md';
import ThemeScripts from './content/ThemeScripts.md';
import Languages from './content/Languages.md';
import Export from './content/Export.md';
import ImportSitemap from './content/ImportSitemap.md';
import Webhooks from './content/Webhooks.md';
import ApiDelivery from './content/ApiDelivery.md';
import Theme from './content/Theme.md';
import ThemesInternationalization from './content/ThemesInternationalization.md';
import ThemesConfiguration from './content/ThemesConfiguration.md';
import ThemesPublishing from './content/ThemesPublishing.md';
import ApiConsole from './content/ApiConsole.md';
import ApiData from './content/ApiData.md';
import Headless from './content/Headless.md';
import Users from './content/Users.md';
import SyntaxHighlighting from './content/SyntaxHighlighting.md';
import Tags from './content/Tags.md';
import NavigationLinks from './content/NavigationLinks.md';
import Import from './content/Import.md';
import ImportWordPress from './content/ImportWordPress.md';
import HyvorTalk from './content/HyvorTalk.md';
import HyvorPost from './content/HyvorPost.md';
import type { NavSectionConfig } from '@hyvor/design/marketing';
import type { Component } from 'svelte';
import { buildI18n, DEFAULT_MARKETING_LANGUAGE } from '../../marketingLang';

export async function getSections(lang: string): Promise<NavSectionConfig[]> {
	async function getComponent(path: string): Promise<Component> {
		const folder = lang ? lang.replace('/', '') : 'content';
		return (await import(`./${folder}/${path}.md`)).default;
	}

	const langCode = lang ? lang.replace('/', '') : DEFAULT_MARKETING_LANGUAGE;
	const i18n = buildI18n(langCode);
	const t = i18n.t.bind(i18n);

	return [
		{
			name: '',
			navs: [
				{
					type: 'page',
					slug: '',
					name: t('docs.pages.introduction'),
					content: await getComponent('Introduction')
				},

				{
					type: 'page',
					slug: 'writing',
					name: t('docs.pages.writing'),
					content: await getComponent('Writing')
				},

				{
					type: 'page',
					slug: 'theme',
					name: t('docs.pages.theme'),
					content: await getComponent('Theme')
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
					content: await getComponent('SubDirectoryHosting')
				},
				{
					type: 'page',
					slug: 'headless',
					name: t('docs.pages.headless'),
					content: await getComponent('Headless')
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
					content: await getComponent('Languages')
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
					content: await getComponent('Tags')
				},
				{
					type: 'page',
					slug: 'media',
					name: t('docs.pages.media'),
					content: await getComponent('Media')
				},
				{
					type: 'page',
					slug: 'fonts',
					name: t('docs.pages.fonts'),
					content: await getComponent('Fonts')
				},
				{
					type: 'page',
					slug: 'seo',
					name: t('docs.pages.seo'),
					content: await getComponent('Seo')
				},
				{
					type: 'page',
					slug: 'navigation-links',
					name: t('docs.pages.navigationLinks'),
					content: await getComponent('NavigationLinks')
				},
				{
					type: 'page',
					slug: 'redirects',
					name: t('docs.pages.redirects'),
					content: await getComponent('Redirect')
				},
				{
					type: 'page',
					slug: 'custom-code',
					name: t('docs.pages.customCode'),
					content: await getComponent('CustomCode')
				},
				{
					type: 'page',
					slug: 'services',
					name: t('docs.pages.services'),
					content: await getComponent('Services')
				},
				{
					type: 'page',
					slug: 'routes',
					name: t('docs.pages.routes'),
					content: await getComponent('Routes')
				},
				{
					type: 'page',
					slug: 'syntax-highlighting',
					name: t('docs.pages.syntaxHighlighting'),
					content: await getComponent('SyntaxHighlighting')
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
					content: await getComponent('HyvorTalk')
				},
				{
					type: 'page',
					slug: 'hyvor-post',
					name: t('docs.pages.hyvorPost'),
					content: await getComponent('HyvorPost')
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
					content: await getComponent('Webhooks')
				},
				{
					type: 'page',
					slug: 'api-console',
					name: t('docs.pages.apiConsole'),
					content: await getComponent('ApiConsole')
				},
				{
					type: 'page',
					slug: 'api-delivery',
					name: t('docs.pages.apiDelivery'),
					content: await getComponent('ApiDelivery')
				},
				{
					type: 'page',
					slug: 'api-data',
					name: t('docs.pages.apiData'),
					content: await getComponent('ApiData')
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
					content: await getComponent('Export')
				},
				{
					type: 'folding-section',
					name: t('docs.pages.importData'),
					navs: [
						{
							type: 'page',
							slug: 'import',
							name: t('docs.pages.importOverview'),
							content: await getComponent('Import')
						},
						{
							type: 'page',
							slug: 'import-sitemap',
							name: t('docs.pages.importSitemap'),
							content: await getComponent('ImportSitemap')
						},
						{
							type: 'page',
							slug: 'import-wordpress',
							name: t('docs.pages.importWordpress'),
							content: await getComponent('ImportWordPress')
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
					content: await getComponent('ThemesOverview')
				},

				{
					type: 'page',
					slug: 'themes-templates',
					name: t('docs.pages.themesTemplates'),
					content: await getComponent('ThemesTemplates')
				},

				{
					type: 'page',
					slug: 'themes-styles',
					name: t('docs.pages.themesStyling'),
					content: await getComponent('ThemesStyles')
				},

				{
					type: 'page',
					slug: 'themes-scripts',
					name: t('docs.pages.themesScripts'),
					content: await getComponent('ThemeScripts')
				},

				{
					type: 'page',
					slug: 'themes-internationalization',
					name: t('docs.pages.themesInternationalization'),
					content: await getComponent('ThemesInternationalization')
				},

				{
					type: 'page',
					slug: 'themes-config',
					name: t('docs.pages.themesConfig'),
					content: await getComponent('ThemesConfiguration')
				},

				{
					type: 'page',
					slug: 'themes-publishing',
					name: t('docs.pages.themesPublishing'),
					content: await getComponent('ThemesPublishing')
				}
			]
		}
	];
}

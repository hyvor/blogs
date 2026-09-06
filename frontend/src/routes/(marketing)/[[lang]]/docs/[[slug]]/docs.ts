import CustomDomain from './content/CustomDomain.md';
import Users from './content/Users.md';
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
					type: 'sub-section',
					name: 'Writing',
					sections: [
						{
							name: '',
							navs: [
								{
									type: 'page',
									slug: 'writing',
									name: t('docs.pages.writing'),
									content: await getComponent('Writing')
								},
								{
									type: 'page',
									slug: 'editor',
									name: 'Editor',
									content: await getComponent('Editor')
								},
								{
									type: 'page',
									slug: 'suggestion-mode',
									name: 'Suggestion Mode',
									content: await getComponent('SuggestionMode')
								},
							]
						},
						{
							name: 'Post Health',
							navs: [
								{
									type: 'page',
									slug: 'seo-analyzer',
									name: 'SEO Analyzer',
									content: await getComponent('SeoAnalyzer')
								},
								{
									type: 'page',
									slug: 'link-analyzer',
									name: 'Link Analyzer',
									content: await getComponent('LinkAnalyzer')
								}
							]
						}
					]
				},

				{
					type: 'sub-section',
					name: t('docs.pages.themes'),
					sections: [
						{
							name: '',
							navs: [
								{
									type: 'page',
									slug: 'themes',
									name: t('docs.pages.themes'),
									content: await getComponent('Themes')
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
					]
				},

				{
					type: 'page',
					slug: 'agent',
					name: 'Agent (AI)',
					content: await getComponent('AIAgent')
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

		
	];
}

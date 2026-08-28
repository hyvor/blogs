<script lang="ts">
	import { AllFeaturesAccordion } from '@hyvor/design/marketing';
	import IconArrowsAngleContract from '@hyvor/icons/IconArrowsAngleContract';
	import IconBraces from '@hyvor/icons/IconBraces';
	import IconCode from '@hyvor/icons/IconCode';
	import IconController from '@hyvor/icons/IconController';
	import IconDatabase from '@hyvor/icons/IconDatabase';
	import IconDiagram3 from '@hyvor/icons/IconDiagram3';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconImage from '@hyvor/icons/IconImage';
	import IconLightning from '@hyvor/icons/IconLightning';
	import IconMarkdown from '@hyvor/icons/IconMarkdown';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconPeople from '@hyvor/icons/IconPeople';
	import IconRegex from '@hyvor/icons/IconRegex';
	import IconRss from '@hyvor/icons/IconRss';
	import IconSearchHeart from '@hyvor/icons/IconSearchHeart';
	import IconSend from '@hyvor/icons/IconSend';
	import IconSignTurnSlightRight from '@hyvor/icons/IconSignTurnSlightRight';
	import IconSignpost2 from '@hyvor/icons/IconSignpost2';
	import IconTag from '@hyvor/icons/IconTag';
	import { getMarketingI18n } from '../../[[lang]]/marketingLang';

	const I18n = getMarketingI18n();

	const t = (key: string) => I18n.t(`homepage.allFeatures.categories.${key}` as never);

	const categories = [
		{
			label: t('postEditor.label'),
			icon: IconPencil,
			color: 'var(--green)',
			features: [
				{
					icon: IconPencil,
					title: t('postEditor.basics.title'),
					description: t('postEditor.basics.description')
				},
				{
					icon: IconImage,
					title: t('postEditor.images.title'),
					description: t('postEditor.images.description')
				},
				{
					icon: IconCode,
					title: t('postEditor.embeds.title'),
					description: t('postEditor.embeds.description')
				},
				{
					icon: IconRegex,
					title: t('postEditor.syntaxHighlighting.title'),
					description: t('postEditor.syntaxHighlighting.description')
				},
				{
					icon: IconMarkdown,
					title: t('postEditor.markdown.title'),
					description: t('postEditor.markdown.description')
				},
				{
					icon: IconHourglass,
					title: t('postEditor.drafts.title'),
					description: t('postEditor.drafts.description')
				}
			]
		},
		{
			label: t('yourBlog.label'),
			icon: IconSignpost2,
			color: 'var(--blue)',
			features: [
				{
					icon: IconTag,
					title: t('yourBlog.tags.title'),
					description: t('yourBlog.tags.description')
				},
				{
					icon: IconPeople,
					title: t('yourBlog.authors.title'),
					description: t('yourBlog.authors.description')
				},
				{
					icon: IconSignpost2,
					title: t('yourBlog.navigation.title'),
					description: t('yourBlog.navigation.description')
				},
				{
					icon: IconSignTurnSlightRight,
					title: t('yourBlog.redirects.title'),
					description: t('yourBlog.redirects.description')
				},
				{
					icon: IconRss,
					title: t('yourBlog.feed.title'),
					description: t('yourBlog.feed.description')
				},
				{
					icon: IconDiagram3,
					title: t('yourBlog.sitemap.title'),
					description: t('yourBlog.sitemap.description')
				},
				{
					icon: IconBraces,
					title: t('yourBlog.customCode.title'),
					description: t('yourBlog.customCode.description')
				}
			]
		},
		{
			label: t('optimizations.label'),
			icon: IconLightning,
			color: 'var(--orange)',
			features: [
				{
					icon: IconSearchHeart,
					title: t('optimizations.seo.title'),
					description: t('optimizations.seo.description')
				},
				{
					icon: IconLightning,
					title: t('optimizations.speed.title'),
					description: t('optimizations.speed.description')
				},
				{
					icon: IconArrowsAngleContract,
					title: t('optimizations.imageOptimization.title'),
					description: t('optimizations.imageOptimization.description')
				}
			]
		},
		{
			label: t('developers.label'),
			icon: IconDatabase,
			color: 'var(--red)',
			features: [
				{
					icon: IconDatabase,
					title: t('developers.dataApi.title'),
					description: t('developers.dataApi.description')
				},
				{
					icon: IconController,
					title: t('developers.consoleApi.title'),
					description: t('developers.consoleApi.description')
				},
				{
					icon: IconSend,
					title: t('developers.deliveryApi.title'),
					description: t('developers.deliveryApi.description')
				},
				{
					icon: IconSend,
					title: t('developers.webhooks.title'),
					description: t('developers.webhooks.description')
				}
			]
		}
	];
</script>

<AllFeaturesAccordion
	title={I18n.t('homepage.allFeatures.title')}
	description={I18n.t('homepage.allFeatures.description')}
	{categories}
/>

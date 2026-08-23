<script lang="ts">
	import { InternationalizationProvider } from '@hyvor/design/components';
	import {
		buildMarketingUrl,
		DEFAULT_MARKETING_LANGUAGE,
		LANGUAGES_CONFIG
	} from './marketingLang';
	import type { PageProps } from './$types';
	import { page } from '$app/state';
	import { MARKETING_PAGE_META } from '../marketing';

	const { children, data }: PageProps = $props();
</script>

<svelte:head>
	{#each data.languageCodes as languageCode}
		<link
			rel="alternate"
			hreflang={languageCode}
			href={`${MARKETING_PAGE_META.urlBasePath}${buildMarketingUrl(page.url.pathname, data.lang, languageCode)}`}
		/>
	{/each}
	<link
		rel="alternate"
		hreflang="x-default"
		href={`${MARKETING_PAGE_META.urlBasePath}${buildMarketingUrl(page.url.pathname, data.lang, DEFAULT_MARKETING_LANGUAGE)}`}
	/>
</svelte:head>

{#key data.lang}
	<InternationalizationProvider languages={LANGUAGES_CONFIG} forceLanguage={data.lang}>
		{@render children?.()}
	</InternationalizationProvider>
{/key}

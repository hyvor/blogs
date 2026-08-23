<script lang="ts">
	import { InternationalizationProvider } from '@hyvor/design/components';
	import { LANGUAGES_CONFIG } from './marketingLang';
	import type { PageProps } from './$types';

	const { children, data }: PageProps = $props();
</script>

{#key data.lang}
	<InternationalizationProvider languages={LANGUAGES_CONFIG} forceLanguage={data.lang}>
		{@render children?.()}
	</InternationalizationProvider>
{/key}

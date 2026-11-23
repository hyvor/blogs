<script lang="ts">
	import { page } from '$app/state';

	// docs are also rendered at hyvor.com/blogs (the canonical location), synced from ../docs
	const CANONICAL_BASE = 'https://hyvor.com';

	let { children } = $props();

	const lang = $derived((page.data.lang as string | undefined) ?? 'en');
	const langPrefix = $derived(lang === 'en' ? '' : `/${lang}`);

	const canonical = $derived.by(() => {
		const path = page.url.pathname.slice(langPrefix.length);
		return `${CANONICAL_BASE}${langPrefix}/blogs${path}`;
	});
</script>

<svelte:head>
	<link rel="canonical" href={canonical} />
</svelte:head>

{@render children?.()}

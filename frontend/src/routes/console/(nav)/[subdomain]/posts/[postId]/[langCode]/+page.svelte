<script>
	import { onMount } from 'svelte';
	import { page } from '$app/state';
	import { redirectIfCant } from '../../../../../lib/scope.svelte';
	import PostVariant from './PostVariant.svelte';

	onMount(() => {
		redirectIfCant('posts.read');
	});
</script>

{#key page.params.postId + '-' + page.params.langCode}
	<PostVariant postId={Number(page.params.postId)} langCode={String(page.params.langCode)} />
{/key}

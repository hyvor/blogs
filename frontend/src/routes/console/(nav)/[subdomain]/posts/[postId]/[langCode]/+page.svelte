<script>
	import { page } from '$app/state';
	import PostVariant from './PostVariant.svelte';
</script>

{#key page.params.postId + '-' + page.params.langCode}
	<PostVariant postId={Number(page.params.postId)} langCode={String(page.params.langCode)} />
{/key}

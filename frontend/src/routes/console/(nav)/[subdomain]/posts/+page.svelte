<script lang="ts">
	import { onMount } from 'svelte';
	import { redirectIfCant } from '../../../lib/scope.svelte';
	import Posts from './Posts.svelte';

	onMount(() => {
		redirectIfCant('posts.read');
	});
</script>

<Posts />

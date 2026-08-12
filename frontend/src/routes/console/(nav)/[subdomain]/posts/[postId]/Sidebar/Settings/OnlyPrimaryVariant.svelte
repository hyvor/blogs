<script lang="ts">
	import { postVariantLanguageStore } from '../../../postStore';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();
</script>

<div class:disabled={!$postVariantLanguageStore.is_primary}>
	{@render children?.()}
</div>

<style>
	div.disabled {
		opacity: 0.3;
		pointer-events: none;
	}
</style>

<script lang="ts">
	import { blogStore } from '../../../../../../lib/stores/blogStore';
	interface Props {
		url: string | null;
		ai?: boolean;
	}

	let { url, ai = false }: Props = $props();
</script>

<div class="message-user">
	{#if url}
		<img src={url} alt="user" class:ai />
	{:else}
		<span class="placeholder">
			{($blogStore.subdomain[0] || '').toUpperCase()}
		</span>
	{/if}
</div>

<style>
	.message-user {
		flex-shrink: 0;
	}
	img {
		width: 30px;
		height: 30px;
		border-radius: 50%;
	}
	img.ai {
		padding: 6px;
		background-color: var(--accent-light-mid);
	}
	.placeholder {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 30px;
		height: 30px;
		border-radius: 50%;
		background-color: var(--accent-light-mid);
	}
</style>

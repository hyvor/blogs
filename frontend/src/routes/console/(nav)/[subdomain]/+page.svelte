<script lang="ts">
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconLaptop from '@hyvor/icons/IconLaptop';
	import IconTablet from '@hyvor/icons/IconTablet';

	import { blogStore } from '../../lib/stores/blogStore';
	import { IconButton, Link } from '@hyvor/design/components';

	let type: 'laptop' | 'tablet' = $state('laptop');
	let isLoading = $state(true);
</script>

<div class="preview">
	<div class="navi">
		<div class="left">
			<Link href={$blogStore.url} target="_blank" underline={false} color="text">
				{$blogStore.url.replace(/https?:\/\//, '')}
				{#snippet end()}
					<IconBoxArrowUpRight size={14} />
				{/snippet}
			</Link>
		</div>
		<div class="right">
			<IconButton
				on:click={() => (type = 'laptop')}
				variant={type == 'laptop' ? 'fill' : 'invisible'}><IconLaptop /></IconButton
			>

			<IconButton
				on:click={() => (type = 'tablet')}
				variant={type == 'tablet' ? 'fill' : 'invisible'}><IconTablet /></IconButton
			>
		</div>
	</div>

	<div class="iframe" style="padding: {type === 'laptop' ? 0 : 15}px">
		{#if isLoading}
			<div class="iframe-loader"></div>
		{/if}
		<iframe
			id="preview-iframe"
			src={$blogStore.url}
			style:width={type === 'laptop' ? '100%' : (type === 'tablet' ? 540 : 360) + 'px'}
			style:height={type === 'laptop' ? '100%' : 740 + 'px'}
			style:display={isLoading ? 'none' : 'block'}
			onload={() => (isLoading = false)}
			title="Preview"
		></iframe>
	</div>
</div>

<style>
	.preview {
		width: 100%;
		height: 100%;
		display: flex;
		flex-direction: column;
		border-radius: var(--box-radius);
		background: var(--box-background);
		box-shadow: var(--box-shadow);
	}

	.navi {
		padding: 15px 20px;
		font-size: 16px;
		display: flex;
		align-items: center;
		border-bottom: 1px solid var(--border);
	}
	.left {
		flex: 1;
		font-size: 14px;
		font-weight: 600;
	}

	.iframe {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		overflow: hidden;
		position: relative;
	}

	.iframe-loader {
		position: absolute;
		inset: 0;
		background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
		background-size: 400% 100%;
		animation: shimmer 1.8s ease-in-out infinite;
	}

	@keyframes shimmer {
		0% {
			background-position: 100% 50%;
		}
		100% {
			background-position: 0 50%;
		}
	}

	iframe {
		max-width: 100%;
		max-height: 100%;
		border: none;
		animation: preview-iframe 0.5s;
	}
	@keyframes preview-iframe {
		0% {
			opacity: 0;
		}
		100% {
			opacity: 1;
		}
	}

	@media screen and (max-width: 1200px) {
		.iframe,
		iframe {
			min-height: 600px;
		}
	}
</style>

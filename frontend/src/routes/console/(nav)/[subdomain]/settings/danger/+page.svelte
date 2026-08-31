<script>
	import { Button, SplitControl } from '@hyvor/design/components';
	import IconTrash from '@hyvor/icons/IconTrash';
	import ClearCacheModal from './ClearCacheModal.svelte';
	import DeleteBlogModal from './DeleteBlogModal.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	let isCacheClearing = $state(false);
	let isDeleting = $state(false);
</script>

<div class="danger">
	<SplitControl label={i18n.t('console.settings.danger.clearCache')}>
		{#snippet caption()}
			<div class="caption">
				<T
					key="console.settings.danger.clearCacheCaption"
					params={{ strong: { element: 'strong' } }}
				/>
			</div>
		{/snippet}

		<Button on:click={() => (isCacheClearing = true)}>
			{i18n.t('console.settings.danger.clearCache')}
		</Button>
	</SplitControl>

	<SplitControl label={i18n.t('console.settings.danger.deleteBlog')}>
		{#snippet caption()}
			<div class="caption">
				<T
					key="console.settings.danger.deleteBlogCaption"
					params={{ strong: { element: 'strong' } }}
				/>
			</div>
		{/snippet}

		<Button color="red" on:click={() => (isDeleting = true)}>
			{#snippet start()}
				<IconTrash />
			{/snippet}
			{i18n.t('console.settings.danger.deleteBlog')}
		</Button>
	</SplitControl>
</div>

{#if isCacheClearing}
	<ClearCacheModal bind:show={isCacheClearing} />
{/if}

{#if isDeleting}
	<DeleteBlogModal bind:show={isDeleting} />
{/if}

<style>
	.danger {
		padding: 20px 30px;
	}
	.caption {
		color: var(--text-light);
		font-size: 14px;
	}
</style>

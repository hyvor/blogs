<script lang="ts">
	import { Button, Dropdown } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { blogStore } from '../../../../../../lib/stores/blogStore';
	import { postVariantLanguageStore, postStore, postVariantStore } from '../../../postStore';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	let showDropdown = $state(false);

	function handleOpenNewTab(url: string) {
		window.open(url, '_blank');
	}

	let previewUrl = $derived(
		$blogStore.url + '/p/' + $postStore.preview_id + '/' + $postVariantLanguageStore.code
	);

	function handleClick(e: MouseEvent) {
		e.stopPropagation();

		if ($postVariantStore.status === 'published') {
			showDropdown = true;
		} else {
			handleOpenNewTab(previewUrl);
		}
	}
</script>

<Dropdown align="center" bind:show={showDropdown} width={200}>
	{#snippet trigger()}
		<Button size="small" color="input" on:click={handleClick}>
			{$postVariantStore.status === 'published'
				? i18n.t('console.postEditor.preview.view')
				: i18n.t('console.postEditor.preview.preview')}
			{#snippet end()}
				<IconBoxArrowUpRight size={12} />
			{/snippet}
		</Button>
	{/snippet}

	{#snippet content()}
		<div class="dropdown-content">
			<Button block color="input" on:click={() => handleOpenNewTab(previewUrl)}>
				{i18n.t('console.postEditor.preview.preview')}
				{#snippet end()}
					<IconBoxArrowUpRight size={12} />
				{/snippet}
			</Button>

			<Button block on:click={() => handleOpenNewTab($postVariantStore.url)}>
				{i18n.t('console.postEditor.preview.publishedPost')}
				{#snippet end()}
					<IconBoxArrowUpRight size={12} />
				{/snippet}
			</Button>
		</div>
	{/snippet}
</Dropdown>

<style>
	.dropdown-content :global(button:nth-child(2)) {
		margin-top: 8px;
		background-color: var(--green-light) !important;
		color: var(--green-dark) !important;
	}
</style>

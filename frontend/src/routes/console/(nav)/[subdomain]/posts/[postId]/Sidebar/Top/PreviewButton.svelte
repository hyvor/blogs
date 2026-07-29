<script lang="ts">
	import { Button, Dropdown } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { blogStore } from '../../../../../../lib/stores/blogStore';
	import { postLanguageStore, postStore, postVariantStore } from '../../../postStore';

	let showDropdown = $state(false);

	function handleOpenNewTab(url: string) {
		window.open(url, '_blank');
	}

	let previewUrl = $derived(
		$blogStore.url + '/p/' + $postStore.preview_id + '/' + $postLanguageStore.code
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
			{$postVariantStore.status === 'published' ? 'View' : 'Preview'}
			{#snippet end()}
				<IconBoxArrowUpRight size={12} />
			{/snippet}
		</Button>
	{/snippet}

	{#snippet content()}
		<div class="dropdown-content">
			<Button block color="input" on:click={() => handleOpenNewTab(previewUrl)}>
				Preview
				{#snippet end()}
					<IconBoxArrowUpRight size={12} />
				{/snippet}
			</Button>

			<Button block on:click={() => handleOpenNewTab($postVariantStore.url)}>
				Published Post
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

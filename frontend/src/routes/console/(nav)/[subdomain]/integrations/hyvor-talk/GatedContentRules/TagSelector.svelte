<script lang="ts">
	import { Button, Dropdown } from '@hyvor/design/components';
	import type { Tag } from '../../../../../lib/types';
	import { IconCaretDown } from '@hyvor/icons';
	import TagsSearch from '../../../posts/[postId]/Sidebar/Settings/Tags/TagsSearch.svelte';
	import TagName from '../../../settings/tags/TagName.svelte';

	let showDropdown = $state(false);

	interface Props {
		selectedTags?: Tag[];
		tag: Tag | null;
		disabled?: boolean;
	}

	let { selectedTags = [], tag = $bindable(), disabled = false }: Props = $props();

	function onSelect(e: CustomEvent<Tag>) {
		tag = e.detail;
		showDropdown = false;
	}
</script>

<Dropdown bind:show={showDropdown} width={300}>
	{#snippet trigger()}
		<Button  color="input" size="small" {disabled}>
			{#if tag}
				<TagName {tag} />
			{:else}
				Select Tag
			{/if}
			{#snippet end()}
				<IconCaretDown  size={14} />
			{/snippet}
		</Button>
	{/snippet}

	{#snippet content()}
		<div >
			<TagsSearch on:select={onSelect} createPrivate={true} {selectedTags} />
		</div>
	{/snippet}
</Dropdown>

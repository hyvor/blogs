<script lang="ts">
	import { Button, Dropdown } from '@hyvor/design/components';
	import type { Tag } from '../../../../lib/types';
	import { IconCaretDown } from '@hyvor/icons';
	import TagsSearch from '../../../posts/[postId]/Sidebar/Settings/Tags/TagsSearch.svelte';

	let showDropdown = false;
	export let tag: Tag;

	function onSelect(e: CustomEvent<Tag>) {
		tag = e.detail;
		showDropdown = false;
	}
</script>

<Dropdown bind:show={showDropdown} width={300}>
	<Button slot="trigger" color="input" size="small">
		{#if tag}
			{tag.variants[0]?.name || 'Select Tag'}
		{:else}
			Select Tag
		{/if}
		<IconCaretDown slot="end" size={14} />
	</Button>

	<div slot="content">
		<TagsSearch on:select={onSelect} />
	</div>
</Dropdown>

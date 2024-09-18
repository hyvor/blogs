<script lang="ts">
	import { Button, Dropdown } from '@hyvor/design/components';
	import type { Tag } from '../../../../lib/types';
	import { IconCaretDown } from '@hyvor/icons';
	import TagsSearch from '../../../posts/[postId]/Sidebar/Settings/Tags/TagsSearch.svelte';
	import TagName from '../../../settings/tags/TagName.svelte';

	let showDropdown = false;

	export let selectedTags: Tag[] = [];
	export let tag: Tag | null;
	export let disabled = false;

	function onSelect(e: CustomEvent<Tag>) {
		tag = e.detail;
		showDropdown = false;
	}
</script>

<Dropdown bind:show={showDropdown} width={300}>
	<Button slot="trigger" color="input" size="small" {disabled}>
		{#if tag}
			<TagName {tag} />
		{:else}
			Select Tag
		{/if}
		<IconCaretDown slot="end" size={14} />
	</Button>

	<div slot="content">
		<TagsSearch on:select={onSelect} createPrivate={true} {selectedTags} />
	</div>
</Dropdown>

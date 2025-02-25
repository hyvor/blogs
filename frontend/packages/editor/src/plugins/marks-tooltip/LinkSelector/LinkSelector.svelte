<script lang="ts">
	import { Modal, TabNav, TabNavItem } from '@hyvor/design/components';
	import IconHash from '@hyvor/icons/IconHash';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconSearch from '@hyvor/icons/IconSearch';

	import Paste from './Paste.svelte';
	import SearchPosts from './SearchPosts.svelte';
	import type { EditorView } from 'prosemirror-view';
	import { toggleMark } from 'prosemirror-commands';
	import schema from '../../../schema';
	import { TextSelection } from 'prosemirror-state';
	import Anchors from './Anchors.svelte';

	interface Props {
		show: boolean;
		view: EditorView;
		edit?: null | string;
	}

	let { show = $bindable(), view, edit = null }: Props = $props();

	let activeTab: 'paste' | 'anchors' | 'posts' = $state('paste');

	function handleAdd(e: CustomEvent<string>) {
		if (edit) {
			// remove the link
			toggleMark(schema.marks.link!)(view.state, view.dispatch);
		}

		toggleMark(schema.marks.link!, { href: e.detail })(view.state, view.dispatch);
		show = false;
		view.focus();

		if (!edit) focusAtLinkEnd();
	}

	function focusAtLinkEnd() {
		const tr = view.state.tr;
		const selection = TextSelection.create(tr.doc, view.state.selection.to);
		view.dispatch(tr.setSelection(selection).scrollIntoView());
		view.focus();
	}
</script>

<Modal bind:show>
	{#snippet title()}
		<TabNav bind:active={activeTab}>
			<TabNavItem name="paste">
				{#snippet start()}
					<IconLink45deg />
				{/snippet}
				Paste Link
			</TabNavItem>
			<TabNavItem name="anchors">
				{#snippet start()}
					<IconHash />
				{/snippet}
				Anchors
			</TabNavItem>
			<TabNavItem name="posts">
				{#snippet start()}
					<IconSearch size={13} />
				{/snippet}
				Posts
			</TabNavItem>
		</TabNav>
	{/snippet}

	{#if activeTab === 'paste'}
		<Paste on:add={handleAdd} input={edit || ''} />
	{:else if activeTab === 'anchors'}
		<Anchors on:add={handleAdd} />
	{:else if activeTab === 'posts'}
		<SearchPosts on:add={handleAdd} />
	{/if}
</Modal>

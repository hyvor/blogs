<script lang="ts">
	import { Modal, TabNav, TabNavItem } from "@hyvor/design/components";
	import { IconLink45deg, IconSearch } from "@hyvor/icons";
	import { tick } from "svelte";
	import Paste from "./Paste.svelte";
	import SearchPosts from "./SearchPosts.svelte";
	import type { EditorView } from "prosemirror-view";
	import { toggleMark } from "prosemirror-commands";
	import schema from "../../../../../../../../lib/prosemirror/schema";
	import { TextSelection } from "prosemirror-state";

    export let show: boolean;
    export let view: EditorView;

    let activeTab: 'paste' | 'posts' = 'paste';

    function handleAdd(e: CustomEvent<string>) {
        toggleMark(schema.marks.link!, {href: e.detail})(view.state, view.dispatch)
        show = false;

        view.focus();
        focusAtLinkEnd();
    }

    function focusAtLinkEnd() {
        const tr = view.state.tr;
        const selection = TextSelection.create(tr.doc,  view.state.selection.to);
        view.dispatch(tr.setSelection(selection).scrollIntoView());
        view.focus();
    }

</script>


<Modal
    bind:show
>

    <TabNav bind:active={activeTab} slot="title">
        <TabNavItem name="paste">
            <IconLink45deg slot="start" />
            Paste Link
        </TabNavItem>
        <TabNavItem name="posts">
            <IconSearch slot="start" size={13} />
            Search Posts
        </TabNavItem>
    </TabNav>

    {#if activeTab === 'paste'}
        <Paste on:add={handleAdd} />
    {:else}
        <SearchPosts on:add={handleAdd} />
    {/if}

</Modal>
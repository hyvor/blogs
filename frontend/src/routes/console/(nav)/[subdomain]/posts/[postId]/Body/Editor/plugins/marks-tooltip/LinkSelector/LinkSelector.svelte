<script lang="ts">
	import { Modal, TabNav, TabNavItem } from "@hyvor/design/components";
	import { IconHash, IconLink45deg, IconSearch } from "@hyvor/icons";
	import Paste from "./Paste.svelte";
	import SearchPosts from "./SearchPosts.svelte";
	import type { EditorView } from "prosemirror-view";
	import { toggleMark } from "prosemirror-commands";
	import schema from "../../../../../../../../../lib/prosemirror/schema";
	import { TextSelection } from "prosemirror-state";
	import Anchors from "./Anchors.svelte";

    export let show: boolean;
    export let view: EditorView;
    export let edit : null | string = null;

    let activeTab: 'paste' | 'anchors' | 'posts' = 'paste';

    function handleAdd(e: CustomEvent<string>) {
        if (edit) {
            // remove the link
            toggleMark(schema.marks.link!)(view.state, view.dispatch);
        }

        toggleMark(schema.marks.link!, {href: e.detail})(view.state, view.dispatch)
        show = false;
        view.focus();

        if (!edit)
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
        <TabNavItem name="anchors">
            <IconHash slot="start" />
            Anchors
        </TabNavItem>
        <TabNavItem name="posts">
            <IconSearch slot="start" size={13} />
            Posts
        </TabNavItem>
    </TabNav>

    {#if activeTab === 'paste'}
        <Paste on:add={handleAdd} input={edit || ''} />
    {:else if activeTab === 'anchors'}
        <Anchors on:add={handleAdd} />
    {:else if activeTab === 'posts'}
        <SearchPosts on:add={handleAdd} />
    {/if}

</Modal>
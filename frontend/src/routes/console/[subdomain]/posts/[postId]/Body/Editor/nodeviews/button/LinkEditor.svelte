<script lang="ts">
	import { IconLink } from "@hyvor/icons";
	import LinkSelector from "../../plugins/marks-tooltip/LinkSelector/LinkSelector.svelte";
	import { EditorView } from "prosemirror-view";
	import { onMount, afterUpdate } from 'svelte';
	import { Modal } from "@hyvor/design/components";
	import Paste from "../../plugins/marks-tooltip/LinkSelector/Paste.svelte";

    export let href: string
    export let changeAttr: (name: string, value: string) => void;

    let show = false;

    function handleAdd(e: CustomEvent<string>) {
        changeAttr('href', e.detail);
        show = false;
    }

</script>

<div>
    {#if show}
        <Modal bind:show>
            <Paste on:add={handleAdd} input={href}/>
        </Modal>
    {/if}
    <button 
        class="link-editor"
        on:click={() => show = true}
    >
        <IconLink />
    </button>
</div>

<style>
    .link-editor {
        width: 20px;
        height: 20px;
        background-color: red;
        border-radius: 50%;
        align-items: center;
    }
</style>

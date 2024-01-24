<script lang="ts">
	import { Modal, Textarea } from "@hyvor/design/components";
	import CodemirrorEditor from "./CodemirrorEditor.svelte";
	import { createEventDispatcher } from "svelte";

    export let value: string
    export let title: string;

    let modalOpen = false;

    const dispatch = createEventDispatcher<{
        change: string,
        confirm: void
    }>();

    function handleConfirm() {
        modalOpen = false;
        dispatch('confirm');
    }

    function handleEditorChange(e: any) {
        dispatch('change', e.detail);
    }

</script>


<div class="wrap">
    <Textarea
        block
        rows={4}
        value={value}
        style="resize:none"
    />
    <div 
        class="overlay" 
        on:click={() => modalOpen = true}
        on:keyup={e => e.key === 'Enter' && (modalOpen = true)}
        role="button"
        tabindex="0"
    />
</div>

{#if modalOpen}
    <Modal
        title={title}
        size="large"
        bind:show={modalOpen}
        footer={{
            confirm: {
                text: "Save",
            }
        }}
        on:confirm={handleConfirm}
    >

        <div class="codemirror-wrap">
            <CodemirrorEditor
                id="code"
                ext="twig"
                value={value}
                on:change={handleEditorChange}
            />
        </div>

    </Modal>
{/if}

<style>
    .wrap {
        position: relative;
    }
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        cursor: pointer;
    }
    .codemirror-wrap {
        height: 100%;
        overflow: auto;
    }
    .codemirror-wrap :global(.CodeMirror) {
        min-height: 400px;
    }
</style>
<script lang="ts">
	import { onMount, tick } from "svelte";
	import { postEditingStatusStore, postVariantStore, updatePostEditingStatusValue, updatePostVariantStore } from "../../../postStore";
	import { IconLock, IconUnlock } from "@hyvor/icons";

    $: show = $postVariantStore.status === 'published' && 
        !$postEditingStatusStore.isEditingPublished;

    let messageEl : HTMLDivElement;
    let overlayEl : HTMLDivElement;

    function positionMessage() {
        if (!messageEl || !overlayEl) return;

        const { top } = overlayEl.getBoundingClientRect();

        messageEl.style.top = (top <= 0 ? -top : 0) + 'px';
        messageEl.style.height = Math.min(window.innerHeight, window.innerHeight - top) + 'px';
    }

    onMount(positionMessage);

    $: show, positionMessage();

    async function handleClick() {
        updatePostEditingStatusValue('isEditingPublished', true);
        await tick();
        $postEditingStatusStore.editorView?.focus();
    }

</script>

<svelte:window on:scroll|capture={positionMessage} />

{#if show}
    <div 
        class="overlay" 
        bind:this={overlayEl}
        on:click={handleClick}
        role="button"
        tabindex="0"
        on:keyup
    />
    <div 
        class="message"
        bind:this={messageEl}
    >   
        <div class="icon">
            <IconLock size={60} />
        </div>

        Post published. Click here to edit.
    </div>
{/if}

<style>
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
        background-color: #fafafa;
        opacity: 0.95;
        transition: .3s opacity;
        cursor: pointer;
    }
    .message {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        left: 0;
        width: 100%;
        z-index: 2;
        pointer-events: none;
    }

    .icon {
        margin-bottom: 10px;
    }

    .overlay:hover {
        opacity: 0.7;
    }

</style>
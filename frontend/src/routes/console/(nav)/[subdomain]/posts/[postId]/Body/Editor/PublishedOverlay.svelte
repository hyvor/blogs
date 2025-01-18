<script lang="ts">
    import { run, createBubbler } from 'svelte/legacy';

    const bubble = createBubbler();
	import { onMount, tick } from "svelte";
	import { postEditingStatusStore, postVariantStore, updatePostEditingStatusValue, updatePostVariantStore } from "../../../postStore";
	import IconLock from '@hyvor/icons/IconLock';
import IconUnlock from '@hyvor/icons/IconUnlock';


    let show = $derived($postVariantStore.status === 'published' && 
        !$postEditingStatusStore.isEditingPublished);

    let messageEl : HTMLDivElement = $state();
    let overlayEl : HTMLDivElement = $state();

    function positionMessage() {
        if (!messageEl || !overlayEl) return;

        const { top } = overlayEl.getBoundingClientRect();

        messageEl.style.top = (top <= 0 ? -top : 0) + 'px';
        messageEl.style.height = Math.min(window.innerHeight, window.innerHeight - top) + 'px';
    }

    onMount(positionMessage);

    run(() => {
        show, positionMessage();
    });

    async function handleClick() {
        updatePostEditingStatusValue('isEditingPublished', true);
        if ($postVariantStore.content_unsaved === null)
            updatePostVariantStore({content_unsaved: $postVariantStore.content});
        await tick();
        $postEditingStatusStore.editorView?.focus();
    }

</script>

<svelte:window onscrollcapture={positionMessage} />

{#if show}
    <div 
        class="overlay" 
        bind:this={overlayEl}
        onclick={handleClick}
        role="button"
        tabindex="0"
        onkeyup={bubble('keyup')}
></div>
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
        transition: .3s transform;
    }

    .overlay:hover + .message .icon {
        transform: scale(1.1);
    } 

</style>
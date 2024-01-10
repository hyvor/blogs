<script lang="ts">
	import type { FormEventHandler } from "svelte/elements";
	import { postVariantStore, updatePostVariantStore } from "../../../postStore";
	import { onMount, tick } from "svelte";

    const handleInput: FormEventHandler<HTMLTextAreaElement> = (event) => {
        updatePostVariantStore({
            title: event.currentTarget.value,
        });
    };

    let textarea: HTMLTextAreaElement;

    function handleResize() {
        if (!textarea)
            return;
        textarea.style.height = "0"
        textarea.style.height = (textarea.scrollHeight) + "px";
    }

    onMount(() => {
        textarea.style.height = "0"
        textarea.style.height = (textarea.scrollHeight) + "px";
        textarea.addEventListener('input', handleResize);
        textarea.addEventListener('change', handleResize);
        textarea.addEventListener('focus', handleResize);
    });

    let previousTitle = "";

    postVariantStore.subscribe(async (value) => {
        if (value.title !== previousTitle) {
            previousTitle = value.title || '';
            await tick();
            handleResize();
        }
    });

</script>

<div class="title-wrap">

    <textarea
        placeholder="Title..."
        autoFocus={($postVariantStore.title || "") === ""}
        value={$postVariantStore.title}
        on:input={handleInput}
        bind:this={textarea}
    />

</div>

<style>

    .title-wrap {
        flex: 1;
    }

    textarea {
        font-family:inherit;
        padding-top: 10px;
        padding-bottom: 10px;
        font-size: 22px;
        font-weight: 600;
        outline: none;
        word-break: break-all;
        resize: none;
        border: 0;
        display: block;
        width: 100%;
        background: none;
        overflow: hidden;
    }

</style>
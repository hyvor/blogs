<script lang="ts">
	import type { FormEventHandler } from "svelte/elements";
	import { postVariantStore, updatePostVariantStore } from "../../postStore";
	import { onMount } from "svelte";

    const handleInput: FormEventHandler<HTMLTextAreaElement> = (event) => {
        updatePostVariantStore({
            title: event.currentTarget.value,
        });
    };

    let textarea: HTMLTextAreaElement;

    onMount(() => {

        textarea.style.height = "0"
        textarea.style.height = (textarea.scrollHeight) + "px";

        textarea.addEventListener('input', () => {
            textarea.style.height = "0"
            textarea.style.height = (textarea.scrollHeight) + "px";
        })

    });

</script>

<div class="title-wrap hds-box">

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
        padding: 10px 25px;
        margin-bottom: 15px;
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
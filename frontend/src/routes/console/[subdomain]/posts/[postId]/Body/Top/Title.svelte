<script lang="ts">
	import type { FormEventHandler } from "svelte/elements";
	import { postEditingStatusStore, postOriginalVariantStore, postVariantStore, updatePostVariantStore } from "../../../postStore";
	import { onMount, tick } from "svelte";
	import UnsavedTag from "../../Sidebar/Settings/UnsavedTag.svelte";
	import { updatePostVariant } from "../../../postActions";
	import { Loader } from "@hyvor/design/components";

    const handleInput: FormEventHandler<HTMLTextAreaElement> = (event) => {
        updatePostVariantStore({
            title: event.currentTarget.value,
        });
    };

    let loaderState : 'none' | 'loading' | 'success' | 'error' = 'none';

    function handleBlur(e: any) {

        const title = (e.target.value as string).trim();

        if (title === $postOriginalVariantStore.title)
            return;

        if ($postVariantStore.status === 'draft') {
            loaderState = 'loading';
            updatePostVariant({title})
                .catch(err => {
                    loaderState = 'error';
                })
                .finally(() => {
                    loaderState = 'success';
                })
        }
    }

    let textarea: HTMLTextAreaElement;

    function handleResize() {
        if (!textarea)
            return;
        textarea.style.height = "0"
        textarea.style.height = (textarea.scrollHeight) + "px";
    }

    function handleKeydown(e: KeyboardEvent) {
        if (e.key === 'Enter' || e.key === 'ArrowDown') {
            e.preventDefault();
            e.stopPropagation();

            $postEditingStatusStore.editorView?.focus();
        }
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
        on:keydown={handleKeydown}
        on:input={handleInput}
        bind:this={textarea}
        on:blur={handleBlur}
    />

    <div class="loader-wrap">
        <Loader state={loaderState} size="small" />
    </div>

    {#if $postVariantStore.title?.trim() !== $postOriginalVariantStore.title}
        <span class="unsaved-tag">
            <UnsavedTag />
        </span>
    {/if}

</div>

<style>

    .title-wrap {
        flex: 1;
        display: flex;
        align-items: center;
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
        position: relative;
    }

    .unsaved-tag {
        position: absolute;
        bottom: 100%;
        transform: translateY(50%);
        left: 25px;
    }

    .loader-wrap {
        margin-left: 5px;
        width: 20px;
    }

</style>
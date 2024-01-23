<script lang="ts">
	import { onDestroy, onMount } from "svelte";
	import { postCurrentContentKey, postEditingStatusStore, postOriginalVariantStore, postVariantStore, updatePostEditingStatusValue } from "../../../../postStore";
	import { updatePostVariant } from "../../../../postActions";
	import { Tag, toast } from "@hyvor/design/components";
	import { beforeNavigate } from "$app/navigation";
	import UnsavedTag from "../../../Sidebar/Settings/UnsavedTag.svelte";
	import { addEditorEventListener } from "../editorEvents";

    $: key = $postCurrentContentKey as 'content' | 'content_unsaved';
    $: hasChanged = $postVariantStore[key] !== $postOriginalVariantStore[key];


    function save() {

        if (!hasChanged)
            return;

        updatePostEditingStatusValue('isSaving', true);

        updatePostVariant({
            [key]: $postVariantStore[key]
        })  
            .then(() => {
                updatePostEditingStatusValue('isSaving', false);
            })
            .catch(e => {
                toast.error(`Failed to save post content: ${e.message}`);
            })
        
    }

    function handleKeydown(e: KeyboardEvent) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            save();
        }
    }

    let autoSaveInterval: ReturnType<typeof setInterval>;

    onMount(() => {
        addEditorEventListener('blur', save);
        autoSaveInterval = setInterval(save, 15000);
    });

    onDestroy(() => {
        clearInterval(autoSaveInterval);
    });

    beforeNavigate(navigation => {
        if (hasChanged) {
            if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                navigation.cancel();
            }
        }
    })

</script>

<svelte:window on:keydown={handleKeydown} />

<span class="save-text">

    <UnsavedTag 
        show={hasChanged}
        loaderState={$postEditingStatusStore.isSaving ? 'loading' : 'none'}
        size="small"
        addMarginTop={false}
    />

    {#if !hasChanged}
        <Tag size="small" color="green">Saved</Tag>
    {/if}

</span>

<style>
    .save-text {
        font-size: 12px;
        color: var(--text-light);
    }
</style>
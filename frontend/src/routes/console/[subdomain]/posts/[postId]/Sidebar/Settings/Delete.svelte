<script lang="ts">
	import { Button, SplitControl, confirm, toast } from "@hyvor/design/components";
	import { postLanguageStore, postStore, removePostVariantStore, updatePostEditingStatusValue } from "../../../postStore";
	import { IconTrash } from "@hyvor/icons";
	import { deletePost, deletePostVariant } from "../../../postActions";
	import { getPrimaryLanguage } from "../../../../../lib/stores/languagesStore";
	import { goto } from "$app/navigation";
	import { blogStore } from "../../../../../lib/stores/blogStore";
	import { consoleUrlWithBlog } from "../../../../../lib/consoleUrl";

    async function handleClick() {

        if (await confirm({
            title: $postLanguageStore.is_primary ? 'Delete Post' : 'Delete Variant',
            content: ($postLanguageStore.is_primary ? 
                'Are you sure you want to delete this post?' : 
                'Are you sure you want to delete the ' + $postLanguageStore.name + ' variant?') +
                ' This action is IRREVERSIBLE.',
            confirmText: 'Yes, Delete',
            danger: true,
        })) {

            const toastId = toast.loading('Deleting...');

            const func = $postLanguageStore.is_primary ? 
                deletePost :
                deletePostVariant;

            func()
                .then(() => {
                    toast.success(
                        'Deleted' + ($postLanguageStore.is_primary ? ' post' : ' variant'),
                        {id: toastId}
                    );

                    if ($postLanguageStore.is_primary) {
                        goto(consoleUrlWithBlog($postStore.is_page ? '/pages' : '/posts'));
                    } else {
                        const languageId = $postLanguageStore.id;
                        updatePostEditingStatusValue('languageId', getPrimaryLanguage().id);
                        removePostVariantStore(languageId);
                    }

                })
                .catch(() => {
                    toast.error('Failed to delete', {id: toastId});
                })

        }

    }

</script>

<SplitControl>
    <span slot="label">
        Delete
    </span>
    <Button 
        color="red" 
        size="small"
        on:click={handleClick}
    >
        {#if $postLanguageStore.is_primary}
            Delete Post
        {:else}
            Delete {$postLanguageStore.name} Variant
        {/if}
        <IconTrash slot="start" />
    </Button>
</SplitControl>
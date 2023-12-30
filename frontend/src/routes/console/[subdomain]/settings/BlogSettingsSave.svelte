<script lang="ts">
	import { Button, ButtonGroup, Loader, toast } from "@hyvor/design/components";
    import { blogOriginalStore, blogStore } from "../../lib/stores/blogStore";
	import type { Blog, BlogVariant } from "../../lib/types";
	import { updateBlog } from "../../lib/actions/blogActions";
	import { beforeNavigate } from "$app/navigation";

    export let keys : (keyof Blog)[] = [];
    export let variantKeys : (keyof BlogVariant)[] = [];

    let loadingState : 'none' | 'loading' | 'success' | 'error' = 'none';

    $: should = getShouldSave($blogStore, $blogOriginalStore);

    beforeNavigate(navigation => {

        if (should) {
            // TODO: Update to new confirm API
            if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                navigation.cancel();
            }
        }
    })

    function getShouldSave(blog: Blog, blogOriginal: Blog) {

        if (keys.some(key => blog[key] !== blogOriginal[key])) {
            return true;
        }

        if (
            blog.variants.some(
                (variant, i) => 
                    variantKeys.some(key => variant[key] !== blogOriginal.variants[i]![key])
                )
        ) {
            return true;
        }

        return false;
    }

    async function handleSave() {

        loadingState = 'loading';

        const blogUpdate : Partial<Blog> = {}
        keys.map(key => {
            if ($blogStore[key] !== $blogOriginalStore[key]) {
                (blogUpdate as any)[key] = $blogStore[key];
            }
        })

        try {
            await updateBlog(blogUpdate, true);

            loadingState = 'success';
        } catch (e: any) {
            loadingState = 'error';
            toast.error(e.message);
        }

    }
 
</script>

<div class="save">

    <span class="loader-wrap">
        <Loader
            state={loadingState}
            size="small"
        />
    </span>

    <ButtonGroup>

        <Button 
            color="gray"
            disabled={!should}
        >
            Discard
        </Button>

        <Button
            disabled={!should}
            on:click={handleSave}
        >
            Save
        </Button>

    </ButtonGroup>

</div>

<style>
    .save {
        padding: 15px 30px;
        text-align: right;
        border-bottom: 1px solid var(--border);
    }
    .loader-wrap {
        display: inline-flex;
        align-items: center;
        height: 100%;
        vertical-align: middle;
        width: 20px;
    }
</style>
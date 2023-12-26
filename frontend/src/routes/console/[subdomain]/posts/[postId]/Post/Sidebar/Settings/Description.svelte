<script lang="ts">
	import { SplitControl, Textarea } from "@hyvor/design/components";
	import { postVariantStore, updatePostVariantStore } from "../../../../../../lib/stores/postStore";

    function handleInput(e: any) {
        updatePostVariantStore({description: e.target.value});
    }

    function handleBlur(e: any) {

        if ($postVariantStore.status === 'draft') {
            // TODO: Add LoaderState 
            updatePostVariantStore({description: e.target.value});
        }

    }

</script>


<SplitControl>
    <span slot="label">Description</span>
    <Textarea
        block
        rows={4}
        value={$postVariantStore.description || ''}
        on:input={handleInput}
        on:blur={handleBlur}
    />
</SplitControl>
<script lang="ts">
	import { SplitControl, TextInput } from "@hyvor/design/components";
	import { postVariantStore, updatePostVariantStore } from "../../../../../../lib/stores/postStore";

    let error : null | string = null;

    function handleInput(e: any) {
        updatePostVariantStore({slug: e.target.value});
    }

    function handleBlur(e: any) {

    }

</script>


<SplitControl>
    <span slot="label">Slug</span>
    <TextInput
        block
        value={$postVariantStore.slug}
        on:input={handleInput}
        on:blur={handleBlur}
    />
</SplitControl>
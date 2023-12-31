<script lang="ts">
	import { SplitControl, TextInput } from "@hyvor/design/components";
	import { postOriginalStore, postStore, updatePostStore } from "../../../postStore";
	import UnsavedTag from "./UnsavedTag.svelte";


    function handleInput(e: any) {
        const val = e.target.value;
        updatePostStore({
            published_at: val ? Math.floor(new Date(val).getTime() / 1000) : null
        });
    }

</script>

<SplitControl>
    <span slot="label">
        Publish Time

        {#if $postStore.published_at !== $postOriginalStore.published_at}
            <UnsavedTag />
        {/if}
    </span>
    <TextInput 
        block 
        type="datetime-local"
        on:input={handleInput}
    />
</SplitControl>
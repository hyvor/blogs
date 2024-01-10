<script lang="ts">
	import { TextInput } from "@hyvor/design/components";
	import { postListFiltersStore, setFilter } from "../postListStore";

    let value = $postListFiltersStore.search;

    let timeout : null | ReturnType<typeof setTimeout> = null;

    function handleInput(e: any) {
        if (timeout)
            clearTimeout(timeout);
        timeout = setTimeout(() => {
            setFilter('search', e.target.value);
        }, 500);
    }
</script>


<TextInput 
    bind:value={value}
    on:input={handleInput}
    placeholder="Search..."
/>
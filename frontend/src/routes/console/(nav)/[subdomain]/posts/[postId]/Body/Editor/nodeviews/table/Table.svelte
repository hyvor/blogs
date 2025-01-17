<script lang="ts">
	import { createEventDispatcher, onMount } from "svelte";


    let colgroup : HTMLTableColElement | null = null;
    let tbody : HTMLTableSectionElement | null = null;

    const dispatch = createEventDispatcher<{
        load: {
            colgroup: HTMLTableColElement,
            tbody: HTMLTableSectionElement
        }
    }>();

    onMount(() => {

        dispatch("load", {
            colgroup: colgroup!,
            tbody: tbody!
        });

    });

</script>

<table>

    <colgroup bind:this={colgroup} />
    <tbody bind:this={tbody} />

</table>
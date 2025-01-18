<script lang="ts">
	import { createEventDispatcher, onMount } from "svelte";


    let colgroup : HTMLTableColElement | null = $state(null);
    let tbody : HTMLTableSectionElement | null = $state(null);

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

    <colgroup bind:this={colgroup}></colgroup>
    <tbody bind:this={tbody}></tbody>

</table>
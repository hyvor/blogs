<script lang="ts">
	import { IconMessage, Loader } from "@hyvor/design/components";
	import { loadPaddleData, paddleDataPromise } from "../paddleActions";
	import Info from "./Info.svelte";
	import { onMount } from "svelte";

    onMount(() => {
        loadPaddleData();
    })

</script>


{#await $paddleDataPromise}
    <Loader 
        block 
        padding={100} 
    />
{:then data}

    {#if data.info}
        <Info info={data.info} />
    {:else}
        <IconMessage empty message="No active subscription" padding={60} />
    {/if}

{/await}
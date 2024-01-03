<script lang="ts">
	import { Loader } from "@hyvor/design/components";
	import { getPaddleData } from "../paddleActions";
	import Info from "./Info.svelte";

    const promise = getPaddleData();

</script>


{#await promise}
    <Loader 
        block 
        padding={100} 
    />
{:then data}

    {#if data.info}
        <Info info={data.info} />
    {:else}
        <!-- TODO: ADD BlockContainer -->
    {/if}

{/await}
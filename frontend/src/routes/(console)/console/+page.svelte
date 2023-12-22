<script lang="ts">
	import { Loader } from "@hyvor/design/components";
	import Nav from "./Nav/Nav.svelte";

    let isLoading = true;
</script>

<main>

    {#if isLoading}
        <div class="full-loader">
            <Loader size="large" />
        </div>
    {:else}
        <div id="nav">
            <Nav />
        </div>
        <div id="content">
            <slot />
        </div>
    {/if}
    
</main>

<style>
    main {
        display: flex;
        width: 100%;
        height: 100vh;
    }
    .full-loader {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    #nav {
        width: 280px;
        padding: 15px;
    }
    #content {
        padding: 15px;
        padding-left: 0;
        flex: 1;
        height: 100%;
    }
</style>
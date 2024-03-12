<script lang="ts">
    import { page } from "$app/stores";
    import Header from '../Header.svelte';
    import Footer from '../Footer.svelte';
</script>

<Header />

<slot />

{#if $page.url.pathname !== '/themes'}
    <Footer />
{/if}

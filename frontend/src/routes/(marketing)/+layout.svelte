<script lang="ts">
	import Footer from './Footer.svelte';
	import Header from './Header.svelte';
	import { page } from "$app/stores";
</script>

<Header />

<slot />

{#if $page.url.pathname !== '/themes'}

    <div class="footer-wrap">
        <Footer />
    </div>

{/if}

<style>

    .footer-wrap {
        margin-top: 100px;
    }

</style>
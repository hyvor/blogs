<script lang="ts">
	import { Header } from "@hyvor/design/marketing";
    import logo from '$lib/img/logo.png';
	import { Button, DarkToggle } from '@hyvor/design/components';
	import { page } from "$app/stores";
	import { onMount } from "svelte";
	import { APP_URL } from "$lib";

    let loggedIn = false;

    onMount(() => {

        fetch(APP_URL + '/api/auth/check', {
            method: 'POST'
        })
            .then<{is_logged_in: boolean}>(res => res.json())
            .then(res => {
                if (res?.is_logged_in) {
                    loggedIn = true;
                }
            });

    })

</script>


<Header
    logo={logo}
    subName="Blogs"
    darkToggle={false}
>

    <div slot="center">
        <Button 
            as="a" 
            size="small" 
            href="/pricing" 
            variant={$page.url.pathname === '/pricing' ? 'fill-light' : 'invisible'}
        >
            Pricing
        </Button>
        <Button 
            as="a" 
            size="small" 
            href="/docs" 
            variant={$page.url.pathname.startsWith('/docs') ? 'fill-light' : 'invisible'}
        >
            Docs
        </Button>
        <Button 
            as="a" 
            size="small" 
            href="/themes"
            variant={$page.url.pathname === '/themes' ? 'fill-light' : 'invisible'}
        >
            Themes
        </Button>
        <Button 
            as="a" 
            size="small" 
            href="/customers" 
            variant={$page.url.pathname === '/customers' ? 'fill-light' : 'invisible'}
        >
            Customers
        </Button>

        <!-- button for integrations -->
        <Button 
            as="a" 
            size="small" 
            href="/integrations" 
            variant={$page.url.pathname === '/integrations' ? 'fill-light' : 'invisible'}
        >
            Integrations
        </Button>
    </div>

    <div slot="end">
        {#if loggedIn}
            <Button as="a" size="small" href="/console">
                Go to Console &rarr;
            </Button>
        {:else}
            <!-- <DarkToggle /> -->
            <Button as="a" size="small" href="/console" variant="invisible">
                Login
            </Button>
            <Button as="a" size="small" href="/console?signup">
                Start a Blog
            </Button>
        {/if}
    </div>

</Header>

<style>
    div[slot="end"] {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* mobile styles */
    @media (max-width: 768px) {
        div[slot="center"] {
            display: flex;
            flex-direction: column;
        }

        div[slot="end"] {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
    }
</style>
<script lang="ts">
	import Header from './Header.svelte';
    import { Footer, FooterLinkList } from '@hyvor/design/marketing';
	import { page } from "$app/stores";
</script>

<Header />

<slot />

{#if $page.url.pathname !== '/themes'}

    <div class="footer-wrap">
        <Footer
            email="blogs.support@hyvor.com"
            social={{
                x: 'https://x.com/HyvorBlogs',
            }}
        >

            <div slot="center">

                <div style="display:flex">
                    <FooterLinkList title="Product">
                        <a href="/console">Console</a>
                        <a href="/themes">Themes</a>
                        <a href="/pricing">Pricing</a>
                        <a href="/docs">Docs</a>
                        <a href="/customers">Customers</a>
                    </FooterLinkList>

                    <FooterLinkList title="Legal">
                        <a href="/docs/terms">Terms of Service</a>
                        <a href="/docs/privacy-policy">Privacy Policy</a>
                    </FooterLinkList>

                    <FooterLinkList title="HYVOR">
                        <a href="https://hyvor.com" target="_blank">hyvor.com</a>
                        <a href="https://hyvor.com/about" target="_blank">About</a>
                        <a href="https://status.hyvor.com" target="_blank">System Status</a>
                    </FooterLinkList>
                </div>

            </div>

        </Footer>
    </div>

{/if}

<style>

    .footer-wrap {
        margin-top: 100px;
    }

</style>
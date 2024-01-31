<script lang="ts">
	import { Button, DarkToggle } from '@hyvor/design/components';
    import { Footer, FooterLinkList, Header } from '@hyvor/design/marketing';
    import logo from '$lib/img/logo.png';
	import { page } from "$app/stores";
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
    </div>

    <div slot="end">
        <!-- <DarkToggle /> -->
        <Button as="a" size="small" href="/console" variant="invisible">
            Login
        </Button>
        <Button as="a" size="small" href="/console?signup">
            Start a Blog
        </Button>
    </div>

</Header>

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

    div[slot="end"] {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .footer-wrap {
        margin-top: 100px;
    }

</style>
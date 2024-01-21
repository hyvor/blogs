<script lang="ts">
	import { Button, DarkToggle } from '@hyvor/design/components';
    import { Footer, FooterLinkList, Header } from '@hyvor/design/marketing';
    import logo from '$lib/img/logo.png';
</script>

<Header
    logo={logo}
    subName="Blogs"
    darkToggle={false}
>

    <div slot="center">
        <Button as="a" size="small" href="/pricing" variant="invisible">
            Pricing
        </Button>
        <Button as="a" size="small" href="/docs" variant="invisible">
            Docs
        </Button>
        <Button as="a" size="small" href="/docs" variant="invisible">
            Themes
        </Button>
        <Button as="a" size="small" href="/customers" variant="invisible">
            Customers
        </Button>
    </div>

    <div slot="end">
        <DarkToggle />
        <Button as="a" size="small" href="/console" variant="invisible">
            Login
        </Button>
        <Button as="a" size="small" href="/console?signup">
            Signup
        </Button>
    </div>

</Header>

<slot />

<div class="footer-wrap">
    <Footer
        email="blogs.support@hyvor.com"
        social={{
            x: 'https://x.com/HyvorBlogs'
        }}
    >

        <div slot="center">

            <div style="display:flex">
                <FooterLinkList title="Product">
                    <a href="/pricing" target="_blank">Pricing</a>
                    <a href="/docs" target="_blank">Docs</a>
                    <a href="/customers" target="_blank">Customers</a>
                </FooterLinkList>

                <FooterLinkList title="HYVOR">
                    <a href="https://hyvor.com" target="_blank">hyvor.com</a>
                </FooterLinkList>
            </div>

        </div>

    </Footer>
</div>

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
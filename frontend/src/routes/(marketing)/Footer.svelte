<script lang="ts">
	import { FooterLinkList, Footer } from '@hyvor/design/marketing';
    
</script>

<Footer
    email="blogs.support@hyvor.com"
    social={{
        x: 'https://x.com/HyvorBlogs',
        youtube: null,
        discord: null,
    }}
>


    <div slot="center">           

        <div class="row first">
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

        <div class="row">
            <FooterLinkList title="Alternatives">
                <a href="https://hyvor.com/blog/wordpress-alternatives" target="_blank">WordPress Alternatives</a>
                <a href="https://hyvor.com/blog/ghost-alternatives" target="_blank">Ghost Alternatives</a>
                <a href="https://hyvor.com/blog/medium-alternatives" target="_blank">Medium Alternatives</a>
                <a href="https://hyvor.com/blog/blogger-alternatives" target="_blank">Blogger Alternatives</a>
            </FooterLinkList>
        </div>

    </div>

</Footer>

<style>
    .row {
        display: flex;
    }
    .row:not(.first) {
        margin-top: 60px;
    }

    @media (max-width: 992px) {
        .row {
            flex-direction: column;
            gap: 35px;
        }
        .row:not(.first) {
            margin-top: 35px;
        }
    }

</style>

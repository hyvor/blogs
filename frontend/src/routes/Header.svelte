<script lang="ts">
    import { Button, DarkToggle } from '@hyvor/design/components';
    import {Header } from '@hyvor/design/marketing';
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
        <DarkToggle />
        <Button as="a" size="small" href="/console" variant="invisible">
            Login
        </Button>
        <Button as="a" size="small" href="/console?signup">
            Start a Blog
        </Button>
    </div>

</Header>
<script lang="ts">
	import { Button, DarkToggle } from '@hyvor/design/components';
    import { Header } from '@hyvor/design/marketing';
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

<style>

    div[slot="end"] {
        display: flex;
        align-items: center;
        gap: 5px;
    }

</style>
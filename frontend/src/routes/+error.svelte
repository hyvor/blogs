<script>
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
        <DarkToggle />
        <Button as="a" size="small" href="/console" variant="invisible">
            Login
        </Button>
        <Button as="a" size="small" href="/console?signup">
            Start a Blog
        </Button>
    </div>

</Header>
 
  
    <main>
      <h1 class="heading">404 - Page Not Found</h1>
      <p class="description">Sorry, the page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
      <Button as="a" size="small" href="/">
        Go to Home
      </Button>
    </main>
  

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
                        <!-- <a href="/docs/terms">Terms</a>
                        <a href="/docs/privacy">Privacy</a> -->
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
   
    main {
      padding: 20px;
      text-align: center;
      height: calc(100vh - 100px);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center; 
    }

    .heading {
      font-size: 38px;
      margin: 0;

    }

    .description {
      /*reduce the width of the text to fit the screen*/
      max-width: 100%;
      width: 450px;
    }

  
  </style>
  
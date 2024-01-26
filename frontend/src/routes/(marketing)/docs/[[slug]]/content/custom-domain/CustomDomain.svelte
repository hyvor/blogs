<script>
	import { Button, Callout, Table, TableRow, Tag, toast } from "@hyvor/design/components";
	import { IconCopy, IconLightbulb } from "@hyvor/icons";
    
    import customDomainSettingsImg from './custom-domain-settings.png';
	import { DocsImage } from "@hyvor/design/marketing";

    const CUSTOM_DOMAIN_IP = '116.202.185.2';
</script>
<h1>Custom Domain</h1>

<p>
    Learn how to set up a custom domain (e.g. <code>blog.example.com</code> or <code>example.com</code>) for your blog.
</p>

<Callout type="info">
    <IconLightbulb slot="icon" />
    Setting up a custom domain will help you to <strong>build your brand</strong> and <strong>prevent locking into our platform</strong>  in case you want to move to another platform in the future.
</Callout>

<p>
    Before setting up a custom domain, you need to have a <strong>domain name</strong>. If you don't have one, you can buy one from a domain registrar like <a href="https://www.namecheap.com/" rel="nofollow">Namecheap</a> or <a href="https://www.cloudflare.com/products/registrar" rel="nofollow">Cloudflare Registrar</a>.
</p>


<h2 id="blog-setitngs">
    Step 1: Update Blog Settings 
</h2>

<ul>
    <li>
        Go to <a href="/console">Console</a> &rarr; Settings &rarr; Hosting.
    </li>
    <li>
        Set <strong>Hosted at</strong> to <strong>Custom Domain</strong>.
    </li>
    <li>
        Then, set your custom domain name.
    </li>
    <li>
        Click <strong>Save</strong>.
    </li>
</ul>

<DocsImage src={customDomainSettingsImg} alt="Custom Domain Settings" />


<h2 id="dns">
    Step 2: Update DNS Records
</h2>

<ul>
    <li>
        Go to your domain registrar's DNS settings.
    </li>
    <li>
        Create a <strong>A</strong> record with the following details.

        <Table columns="1fr 2fr">
            <TableRow>
                <div>Host/Name</div>
                <div>
                    <div style="margin-bottom:6px;">
                        <code>@</code> for <strong>example.com</strong> or
                    </div>
                    <code>blog</code> for <strong>blog.example.com</strong>
                </div>
            </TableRow>
            <TableRow>
                <div>IP Address</div>
                <div>
                    <code>{CUSTOM_DOMAIN_IP}</code>
                    <Button
                        size="x-small"
                        on:click={() => {
                            navigator.clipboard.writeText(CUSTOM_DOMAIN_IP)
                            toast.success('Copied to clipboard')
                        }}
                        style="margin-left:5px;"
                        color="input"
                    >
                        Copy <IconCopy slot="end" size={12} />
                    </Button>
                </div>
            </TableRow>
        </Table>
    </li>
</ul>

<p>
    Voila! Your blog is now available at your custom domain.
</p>


<h2 id="troubleshoot">
    Troubleshooting
</h2>

<ul>
    <li>
        If your blog with custom domain is loading infinitely or returning any other errors codes, make sure you do not have any other <code>A</code> or <code>AAAA</code> records with the same hostname as your custom domain.
    </li>
    <li>
        If you are using <strong>Cloudflare</strong> for your domain, use one the following options.
        <ul>
            <li>
                <Tag size="small" color="green" style="display:inline">Recommended</Tag> &nbsp;

                Turn on the proxy (Orange Cloud) and set the <a href="https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/" target="_blank" rel="nofollow">Encryption Mode</a> to <strong>Full</strong> or <strong>Full (Strict)</strong>. This will enable the Cloudflare global CDN for your blog for better caching and performance.
            </li>
            <li>
                Turn off the proxy (Gray Cloud)
            </li>
        </ul>
    </li>
</ul>
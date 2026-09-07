<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Custom Domain

Learn how to set up a custom domain (e.g. `blog.example.com` or `example.com`) for your blog.

<Callout type="info">
	{#snippet icon()}
		<IconLightbulb />
	{/snippet}
	Setting up a custom domain will help you to <strong>build your own brand</strong> and
	<strong>prevent locking into our platform</strong> in case you want to move to another platform in the
	future.
</Callout>

<h2 id="prerequisites">Prerequisites</h2>

- A **domain name**.
- Access to your domain's **DNS settings** to create DNS records.
- [Admin role](/docs/users#roles) access to your blog.

<h2 id="blog-setitngs">Step 1: Update Blog Settings</h2>

- Go to [Console](/console) &rarr; Settings &rarr; Hosting.
- Click **Setup Custom Domain**
- Enter your custom domain
- Click Save

<DocsImage
	src="/images/docs/custom-domain/custom-domain-settings.png"
	alt="Custom Domain Settings"
/>

<Callout type="info">
	{#snippet icon()}
		<IconLightbulb />
	{/snippet}
	You can add your own TLS certificate and private key for your domain by selecting <strong>Bring Your Own</strong> option. However, we recommend you to use the <strong>Automatic</strong> option and let Hyvor Blogs handle your TLS certificate through <strong>Let's Encrypt</strong>.
</Callout>

<!--
- Set **Hosted at** to **Custom Domain**.
- Then, set your custom domain name.
- Click **Save**.
 -->
<h2 id="dns">Step 2: Update DNS Records</h2>

Go to your domain registrar's DNS settings and create either a **CNAME** (recommended - easier and more reliable) or an **A** record with the details below.

<!-- TODO -->

Voila! Your blog is now available at your custom domain.

<h2 id="cloudflare">Using Cloudflare</h2>

If you are using Cloudflare for your domain, use one of the following options.

- (Recommended) Turn on the proxy (Orange Cloud) and set the <a href="https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/" target="_blank" rel="nofollow">Encryption Mode</a> to **Full** or **Full (Strict)**. This will enable the Cloudflare global CDN for your blog for better caching and performance.
- Turn off the proxy (Gray Cloud)

<h2 id="troubleshoot">Troubleshooting</h2>

If your blog with custom domain is loading infinitely or returning any other errors codes, please check the following.

- Make sure you do not have any other `A` or `AAAA` records with the same hostname as your custom domain.
- If you have set up `CAA` records for your domain, make sure you have allowed ZeroSSL to issue certificates for your domain (see <a href="https://help.zerossl.com/hc/en-us/articles/360060119753-Invalid-CAA-Records" target="_blank">this guide</a>). **HOWEVER**, please note that in the event of a change in our certificate provider, you may need to update your CAA records accordingly. Therefore, if possible, we recommend not to use CAA records for your domain.

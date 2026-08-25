<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
	import { DocsImage } from '@hyvor/design/marketing';
	import DnsInstructions from '../../../../../../console/(nav)/[subdomain]/settings/hosting/DnsInstructions.svelte';
</script>

<h1>Custom Domain</h1>

<p>
	Learn how to set up a custom domain (e.g. <code>blog.example.com</code> or
	<code>example.com</code>) for your blog.
</p>

<Callout type="info">
	{#snippet icon()}
		<IconLightbulb />
	{/snippet}
	Setting up a custom domain will help you to <strong>build your own brand</strong> and
	<strong>prevent locking into our platform</strong> in case you want to move to another platform in the
	future.
</Callout>

<h2 id="prerequisites">Prerequisites</h2>

<ul>
	<li>
		A <strong>domain name</strong>.
	</li>
	<li>
		Access to your domain's <strong>DNS settings</strong> to create DNS records.
	</li>
	<li>
		<a href="/docs/users#roles">Admin role</a> access to your blog.
	</li>
</ul>

<h2 id="blog-setitngs">Step 1: Update Blog Settings</h2>

<ul>
	<li>
		Go to <a href="/console">Console</a> &rarr; Settings &rarr; Hosting.
	</li>
	<li>
		Set <strong>Hosted at</strong> to <strong>Custom Domain</strong>.
	</li>
	<li>Then, set your custom domain name.</li>
	<li>
		Click <strong>Save</strong>.
	</li>
</ul>

<DocsImage
	src="/images/docs/custom-domain/custom-domain-settings.png"
	alt="Custom Domain Settings"
/>

<h2 id="dns">Step 2: Update DNS Records</h2>

<p>
	Go to your domain registrar's DNS settings and create either a <strong>CNAME</strong>
	(recommended - easier and more reliable) or an <strong>A</strong> record with the details below.
</p>

<DnsInstructions />

<p>Voila! Your blog is now available at your custom domain.</p>

<h2 id="cloudflare">Using Cloudflare</h2>

<p>If you are using Cloudflare for your domain, use one of the following options.</p>

<ul>
	<li>
		(Recommended) Turn on the proxy (Orange Cloud) and set the <a
			href="https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/"
			target="_blank"
			rel="nofollow">Encryption Mode</a
		>
		to <strong>Full</strong> or <strong>Full (Strict)</strong>. This will enable the Cloudflare
		global CDN for your blog for better caching and performance.
	</li>
	<li>Turn off the proxy (Gray Cloud)</li>
</ul>

<h2 id="troubleshoot">Troubleshooting</h2>

<p>
	If your blog with custom domain is loading infinitely or returning any other errors codes, please
	check the following.
</p>

<ul>
	<li>
		Make sure you do not have any other <code>A</code> or <code>AAAA</code> records with the same hostname
		as your custom domain.
	</li>
	<li>
		If you have set up <code>CAA</code> records for your domain, make sure you have allowed ZeroSSL
		to issue certificates for your domain (see
		<a
			href="https://help.zerossl.com/hc/en-us/articles/360060119753-Invalid-CAA-Records"
			target="_blank">this guide</a
		>). <strong>HOWEVER</strong>, please note that in the event of a change in our certificate
		provider, you may need to update your CAA records accordingly. Therefore, if possible, we
		recommend not to use CAA records for your domain.
	</li>
</ul>

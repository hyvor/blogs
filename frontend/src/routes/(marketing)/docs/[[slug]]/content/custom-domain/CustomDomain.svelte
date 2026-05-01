<script lang="ts">
	import {
		Button,
		Callout,
		TabNav,
		TabNavItem,
		Table,
		TableRow,
		Tag,
		toast
	} from '@hyvor/design/components';
	import IconCopy from '@hyvor/icons/IconCopy';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';

	import customDomainSettingsImg from './custom-domain-settings.png';
	import { DocsImage } from '@hyvor/design/marketing';

	const CUSTOM_DOMAIN_IP = '116.202.185.2';
	const CNAME_DOMAIN = 'hyvorblogs.io';

	let dnsMethod: 'cname' | 'a' = $state('cname');
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
	Setting up a custom domain will help you to <strong>build your brand</strong> and
	<strong>prevent locking into our platform</strong> in case you want to move to another platform in the
	future.
</Callout>

<p>
	Before setting up a custom domain, you need to have a <strong>domain name</strong>. If you don't
	have one, you can buy one from a domain registrar like
	<a href="https://www.namecheap.com/" rel="nofollow">Namecheap</a>
	or
	<a href="https://www.cloudflare.com/products/registrar" rel="nofollow">Cloudflare Registrar</a>.
</p>

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

<DocsImage src={customDomainSettingsImg} alt="Custom Domain Settings" />

<h2 id="dns">Step 2: Update DNS Records</h2>

<TabNav>
	<TabNavItem name="cname" active={dnsMethod === 'cname'} onclick={() => (dnsMethod = 'cname')}>
		CNAME {#snippet end()}
			<Tag size="small" color="blue">Preferred</Tag>
		{/snippet}
	</TabNavItem>
	<TabNavItem name="a" active={dnsMethod === 'a'} onclick={() => (dnsMethod = 'a')}
		>A Record</TabNavItem
	>
</TabNav>

{#if dnsMethod === 'cname'}
	<p>
		<strong>Recommended</strong> method. It's easier and more reliable. Go to your domain
		registrar's DNS settings and create a <strong>CNAME</strong> record with the following details.
	</p>

	<Table columns="1fr 2fr">
		<TableRow head>
			<div>Field</div>
			<div>Value</div>
		</TableRow>
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
			<div>Content</div>
			<div>
				<code>{CNAME_DOMAIN}</code>
				<Button
					size="x-small"
					on:click={() => {
						navigator.clipboard.writeText(CNAME_DOMAIN);
						toast.success('Copied to clipboard');
					}}
					style="margin-left:5px;"
					color="input"
				>
					Copy {#snippet end()}
						<IconCopy size={12} />
					{/snippet}
				</Button>
			</div>
		</TableRow>
	</Table>
{:else}
	<p>
		If you can't use the CNAME method, you can use the <strong>A</strong> record method. This method
		depends on our infrastructure. If you use this method, you may need to update the IP address in
		the future if we have a <i>major</i> infrastructure change.
	</p>

	<p>
		Go to your domain registrar's DNS settings and create an <strong>A</strong> record with the following
		details.
	</p>

	<Table columns="1fr 2fr">
		<TableRow head>
			<div>Field</div>
			<div>Value</div>
		</TableRow>
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
						navigator.clipboard.writeText(CUSTOM_DOMAIN_IP);
						toast.success('Copied to clipboard');
					}}
					style="margin-left:5px;"
					color="input"
				>
					Copy {#snippet end()}
						<IconCopy size={12} />
					{/snippet}
				</Button>
			</div>
		</TableRow>
	</Table>
{/if}

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

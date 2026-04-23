<script lang="ts">
	import {
		Callout,
		FormControl,
		InputGroup,
		Link,
		Radio,
		SplitControl,
		Switch,
		TextInput,
		Validation,
		toast, Button, TabNav, Table, TableRow, TabNavItem, Tag
	} from '@hyvor/design/components';
	import { blogOriginalStore, blogStore } from '../../../../lib/stores/blogStore';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';

	import type {Blog, CustomDomainHosting} from '../../../../lib/types';
	import { isSubdomainValid } from '../../../../lib/helper/isSubdomainValid';
	import { isValidUrl } from '../../../../lib/helper/is-valid-url';
	import DisabledOnTemp from '../../Temp/DisabledOnTemp.svelte';
	import {getCustomDomainHosting} from "./hostingActions";
	import IconCopy from "@hyvor/icons/IconCopy";

	const originalSubdomain = $blogStore.subdomain;
	let subdomain = $state($blogStore.subdomain);

	let subdomainError: null | string = $state(null);
	let hostingUrlError: null | string = $state(null);

	let customDomainHosting: null | CustomDomainHosting = $state(null);
	let dnsMethod: 'cname' | 'a' = $state('cname');
	const CUSTOM_DOMAIN_IP = '116.202.185.2';
	const CNAME_DOMAIN = 'hyvorblogs.io';

	function handleBlogValueChangeEvent(e: any, key: keyof Blog) {
		blogStore.update((b) => {
			return {
				...b,
				[key]: e.target.value
			};
		});
	}

	function handleRedirectSubdomainChange() {
		blogStore.update((b) => {
			return {
				...b,
				hosting_redirect_subdomain: !b.hosting_redirect_subdomain
			};
		});
	}

	function handleHostedAtChange(e: any) {
		blogStore.update((b) => {
			return {
				...b,
				hosting_at: e.target.value
			};
		});
	}

	function handleBeforeSave() {
		if (subdomain !== $blogStore.subdomain && subdomainError) {
			toast.error('Subdomain error: ' + subdomainError);
			return false;
		}

		subdomainError = null;
		hostingUrlError = null;

		if ($blogStore.hosting_at === 'self') {
			const hostingUrl = ($blogStore.hosting_url || '').trim();
			if (hostingUrl === '') {
				hostingUrlError = 'Self-Hosting URL is required';
				return false;
			}
			if (!isValidUrl(hostingUrl)) {
				hostingUrlError = 'Invalid URL. Make sure to include the protocol (https://)';
				return false;
			}
		}

		if ($blogStore.hosting_at === 'domain') {
			const hostingDomain = ($blogStore.hosting_domain || '').trim();
			if (hostingDomain === '') {
				hostingUrlError = 'Custom Domain is required';
				return false;
			}
			if (hostingDomain.match(' ')) {
				hostingUrlError = 'Custom Domain cannot contain spaces';
				return false;
			}
			if (hostingDomain.match(/^https?:\/\//)) {
				hostingUrlError = 'Add the domain without the protocol (https://)';
				return false;
			}
			if (hostingDomain.match('/')) {
				hostingUrlError =
					'Custom Domain cannot contain /. Use self-hosting for to host your blog in a subdirectory';
				return false;
			}
		}

		return true;
	}

	function handleAfterSave(newBlog: Blog) {
		setTimeout(() => {
			if (newBlog.subdomain !== originalSubdomain) {
				window.location.href = `/console/${newBlog.subdomain}/settings/hosting`;
			}
		}, 0);
	}

	function handleSubdomainInput(e: any) {
		const val = e.target.value;

		subdomainError = null;

		if (val === $blogStore.subdomain) {
			return;
		}

		const error = isSubdomainValid(val);
		subdomainError = error;
	}

	function handleError(message: string, code: number) {
		if (message === 'domain_taken') {
			hostingUrlError = 'This domain is already taken by another blog. Contact support if needed.';
		} else {
			toast.error(message);
		}
	}

	let hasUrlChanged = $derived(
		$blogStore.subdomain !== subdomain ||
			$blogOriginalStore.hosting_at !== $blogStore.hosting_at ||
			$blogOriginalStore.hosting_domain !== $blogStore.hosting_domain ||
			$blogOriginalStore.hosting_url !== $blogStore.hosting_url
	);

	function getCustomDomainHostingStatus() {
		customDomainHosting = getCustomDomainHosting();
	}

	$effect(() => {
		$blogOriginalStore.hosting_domain && getCustomDomainHostingStatus();
	})
</script>

<DisabledOnTemp>
	<BlogSettingsSave
		keys={[
			'subdomain',
			'hosting_at',
			'hosting_domain',
			'hosting_url',
			'hosting_redirect_subdomain'
		]}
		outsideChanges={subdomain !== $blogStore.subdomain ? { subdomain } : {}}
		beforeSave={handleBeforeSave}
		afterSave={handleAfterSave}
		onError={handleError}
	/>

	<div class="hosting">
		<SplitControl
			label="Subdomain"
			caption="The hyvorblogs.io subdomain. Uniquely identifies your blog within Hyvor Blogs."
		>
			<FormControl>
				<TextInput
					bind:value={subdomain}
					block
					state={subdomainError ? 'error' : undefined}
					on:input={handleSubdomainInput}
				/>
				{#if subdomainError}
					<Validation state="error">{subdomainError}</Validation>
				{/if}
			</FormControl>
		</SplitControl>

		<SplitControl label="Hosted at" caption="Where do you like to host your blog?">
			<InputGroup>
				<Radio value="subdomain" group={$blogStore.hosting_at} on:change={handleHostedAtChange}>
					Subdomain (hyvorblogs.io)
				</Radio>
				<Radio value="domain" group={$blogStore.hosting_at} on:change={handleHostedAtChange}>
					Custom Domain - &nbsp;<Link
						href="/docs/custom-domain"
						target="_blank"
						style="font-size:14px;"
					>
						Docs
						{#snippet end()}
							<IconBoxArrowUpRight size={10} />
						{/snippet}
					</Link>
				</Radio>
				<Radio value="self" group={$blogStore.hosting_at} on:change={handleHostedAtChange}>
					Self-hosting - &nbsp;<Link
						href="/docs/self-hosting"
						target="_blank"
						style="font-size:14px;"
					>
						Docs
						{#snippet end()}
							<IconBoxArrowUpRight size={10} />
						{/snippet}
					</Link>
				</Radio>
			</InputGroup>
		</SplitControl>

		{#if $blogStore.hosting_at === 'domain'}
			<SplitControl label="Custom Domain" caption="Your custom domain name">
				<FormControl>
					<TextInput
						value={$blogStore.hosting_domain}
						on:input={(e) => {
							e.target.value = e.target.value.trim();
							handleBlogValueChangeEvent(e, 'hosting_domain');
						}}
						block
						placeholder="blog.example.com"
						state={hostingUrlError ? 'error' : undefined}
					/>
					{#if hostingUrlError}
						<Validation state="error">{hostingUrlError}</Validation>
					{/if}
				</FormControl>

				{#if $blogOriginalStore.hosting_domain && customDomainHosting}
					<Callout
							type={customDomainHosting.status === 'active' ? 'success' : 'warning'}
							style="margin-top: 20px;"
					>
						{#if customDomainHosting.status !== 'active'}
							<p>
								Your custom domain has not been verified yet. Please update your DNS records as shown
								below to verify your domain ownership.
							</p>

							<TabNav bind:active={dnsMethod}>
								<TabNavItem name="cname">
									CNAME {#snippet end()}
									<Tag size="small" color="blue">Preferred</Tag>
								{/snippet}
								</TabNavItem>
								<TabNavItem name="a">A Record</TabNavItem>
							</TabNav>

							{#if dnsMethod === 'cname'}
								<Table columns="1fr 2fr">
									<br />
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
								<Table columns="1fr 2fr">
									<br />
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

							<div style="margin-top: 15px; margin-bottom: 10px;">
							<Button>
								Verify Now
								{#snippet end()}
									<IconArrowClockwise />
								{/snippet}
							</Button>
							</div>

						{:else}
							Your custom domain is verified and active. 🎉
						{/if}
					</Callout>
				{/if}
			</SplitControl>
		{/if}

		{#if $blogStore.hosting_at === 'self'}
			<SplitControl label="Self-hosting URL" caption="Where your blog is hosted (absolute URL)">
				<FormControl>
					<TextInput
						bind:value={$blogStore.hosting_url}
						on:input={(e) => handleBlogValueChangeEvent(e, 'hosting_url')}
						block
						placeholder="https://example.com/blog"
						state={hostingUrlError ? 'error' : undefined}
					/>
					{#if hostingUrlError}
						<Validation state="error">{hostingUrlError}</Validation>
					{/if}
				</FormControl>
			</SplitControl>
		{/if}

		{#if $blogStore.hosting_at !== 'subdomain'}
			<SplitControl
				label="Redirect Subdomain"
				caption={`Whether to redirect ${$blogStore.subdomain}.hyvorblogs.io to your ` +
					($blogStore.hosting_at === 'domain' ? 'custom domain' : 'self-hosting URL')}
			>
				<Switch
					checked={$blogStore.hosting_redirect_subdomain}
					on:change={handleRedirectSubdomainChange}
				/>
			</SplitControl>
		{/if}

		{#if hasUrlChanged}
			<Callout type="warning" style="margin-top: 20px;">
				{#snippet icon()}
					<IconExclamationCircle size={18} />
				{/snippet}
				{#snippet title()}
					<div>URL Change</div>
				{/snippet}
				You are about to change the URL of your blog!
				<ul>
					<li>
						Previously shared links may break. However, when changing from hyvorblogs.io subdomain
						to a custom domain or self-hosting, we'll redirect users to the new URL.
					</li>
					<li>This may impact the SEO of your blog.</li>
					<li>
						We'll update the media links in your post content and blog settings. This may take some
						time.
					</li>
				</ul>
			</Callout>
		{/if}
	</div>
</DisabledOnTemp>

<style>
	.hosting {
		padding: 20px 30px;
	}
</style>

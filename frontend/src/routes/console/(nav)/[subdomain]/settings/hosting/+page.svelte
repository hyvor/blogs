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
		toast
	} from '@hyvor/design/components';
	import { blogOriginalStore, blogStore } from '../../../../lib/stores/blogStore';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
    import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';

	import type { Blog } from '../../../../lib/types';
	import { isSubdomainValid } from '../../../../lib/helper/isSubdomainValid';
	import { isValidUrl } from '../../../../lib/helper/is-valid-url';
	import DisabledOnTemp from '../../Temp/DisabledOnTemp.svelte';

	const originalSubdomain = $blogStore.subdomain;
	let subdomain = $state($blogStore.subdomain);

	let subdomainError: null | string = $state(null);
	let hostingUrlError: null | string = $state(null);

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

	let hasUrlChanged =
		$derived($blogStore.subdomain !== subdomain ||
		$blogOriginalStore.hosting_at !== $blogStore.hosting_at ||
		$blogOriginalStore.hosting_domain !== $blogStore.hosting_domain ||
		$blogOriginalStore.hosting_url !== $blogStore.hosting_url);
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
												<IconBoxArrowUpRight  size={10} />
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
												<IconBoxArrowUpRight  size={10} />
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
								<IconExclamationCircle  size={18} />
							{/snippet}
				{#snippet title()}
								<div >URL Change</div>
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

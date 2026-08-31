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
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

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
			toast.error(i18n.t('console.settings.hosting.subdomainError', { error: subdomainError }));
			return false;
		}

		subdomainError = null;
		hostingUrlError = null;

		if ($blogStore.hosting_at === 'self') {
			const hostingUrl = ($blogStore.hosting_url || '').trim();
			if (hostingUrl === '') {
				hostingUrlError = i18n.t('console.settings.hosting.validation.selfUrlRequired');
				return false;
			}
			if (!isValidUrl(hostingUrl)) {
				hostingUrlError = i18n.t('console.settings.hosting.validation.invalidUrl');
				return false;
			}
		}

		if ($blogStore.hosting_at === 'domain') {
			const hostingDomain = ($blogStore.hosting_domain || '').trim();
			if (hostingDomain === '') {
				hostingUrlError = i18n.t('console.settings.hosting.validation.domainRequired');
				return false;
			}
			if (hostingDomain.match(' ')) {
				hostingUrlError = i18n.t('console.settings.hosting.validation.domainSpaces');
				return false;
			}
			if (hostingDomain.match(/^https?:\/\//)) {
				hostingUrlError = i18n.t('console.settings.hosting.validation.domainProtocol');
				return false;
			}
			if (hostingDomain.match('/')) {
				hostingUrlError = i18n.t('console.settings.hosting.validation.domainSlash');
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
			hostingUrlError = i18n.t('console.settings.hosting.validation.domainTaken');
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
			label={i18n.t('console.settings.hosting.subdomain')}
			caption={i18n.t('console.settings.hosting.subdomainCaption')}
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

		<SplitControl
			label={i18n.t('console.settings.hosting.hostedAt')}
			caption={i18n.t('console.settings.hosting.hostedAtCaption')}
		>
			<InputGroup>
				<Radio value="subdomain" group={$blogStore.hosting_at} on:change={handleHostedAtChange}>
					{i18n.t('console.settings.hosting.optionSubdomain')}
				</Radio>
				<Radio value="domain" group={$blogStore.hosting_at} on:change={handleHostedAtChange}>
					{i18n.t('console.settings.hosting.optionDomain')} - &nbsp;<Link
						href="/docs/custom-domain"
						target="_blank"
						style="font-size:14px;"
					>
						{i18n.t('console.settings.hosting.docs')}
						{#snippet end()}
							<IconBoxArrowUpRight size={10} />
						{/snippet}
					</Link>
				</Radio>
				<Radio value="self" group={$blogStore.hosting_at} on:change={handleHostedAtChange}>
					{i18n.t('console.settings.hosting.optionSelf')} - &nbsp;<Link
						href="/docs/self-hosting"
						target="_blank"
						style="font-size:14px;"
					>
						{i18n.t('console.settings.hosting.docs')}
						{#snippet end()}
							<IconBoxArrowUpRight size={10} />
						{/snippet}
					</Link>
				</Radio>
			</InputGroup>
		</SplitControl>

		{#if $blogStore.hosting_at === 'domain'}
			<SplitControl
				label={i18n.t('console.settings.hosting.customDomain')}
				caption={i18n.t('console.settings.hosting.customDomainCaption')}
			>
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
			<SplitControl
				label={i18n.t('console.settings.hosting.selfUrl')}
				caption={i18n.t('console.settings.hosting.selfUrlCaption')}
			>
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
				label={i18n.t('console.settings.hosting.redirectSubdomain')}
				caption={i18n.t('console.settings.hosting.redirectSubdomainCaption', {
					subdomain: $blogStore.subdomain,
					target:
						$blogStore.hosting_at === 'domain'
							? i18n.t('console.settings.hosting.customDomainLower')
							: i18n.t('console.settings.hosting.selfUrlLower')
				})}
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
					<div>{i18n.t('console.settings.hosting.urlChange.title')}</div>
				{/snippet}
				{i18n.t('console.settings.hosting.urlChange.intro')}
				<ul>
					<li>{i18n.t('console.settings.hosting.urlChange.point1')}</li>
					<li>{i18n.t('console.settings.hosting.urlChange.point2')}</li>
					<li>{i18n.t('console.settings.hosting.urlChange.point3')}</li>
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

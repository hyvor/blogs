<script lang="ts">
	import {
		Button,
		Callout,
		confirm,
		FormControl,
		Loader,
		SplitControl,
		Switch,
		Tag,
		TextInput,
		toast,
		Validation
	} from '@hyvor/design/components';
	import {
		blogOriginalStore,
		blogStore,
		hostingInfoStore,
		updateHostingInfoStore
	} from '../../../../lib/stores/blogStore';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import type {Blog} from '../../../../lib/types';
	import {isSubdomainValid} from '../../../../lib/helper/isSubdomainValid';
	import DisabledOnTemp from '../../Temp/DisabledOnTemp.svelte';
	import {getHostingInfo, updateHostedAt} from './hostingActions';
	import SetupCustomDomainModal from './SetupCustomDomainModal.svelte';
	import SetupSelfHostingModal from './SetupSelfHostingModal.svelte';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle'
	import {onMount} from "svelte";

	const originalSubdomain = $blogStore.subdomain;
	let subdomain = $state($blogStore.subdomain);

	let subdomainError: null | string = $state(null);
	let showCustomDomainModal = $state(false);
	let showSelfHostingModal = $state(false);

	let isLoading = $state(true);

	function handleRedirectSubdomainChange() {
		blogStore.update((b) => {
			return {
				...b,
				hosting_redirect_subdomain: !b.hosting_redirect_subdomain
			};
		});
	}

	function handleBeforeSave() {
		if (subdomain !== $blogStore.subdomain && subdomainError) {
			toast.error('Subdomain error: ' + subdomainError);
			return false;
		}

		subdomainError = null;
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

		subdomainError = isSubdomainValid(val);
	}

	function handleError(message: string) {
		toast.error(message);
	}

	let hasUrlChanged = $derived(
		$blogStore.subdomain !== subdomain ||
			$blogOriginalStore.hosting_at !== $blogStore.hosting_at ||
			$blogOriginalStore.hosting_domain !== $blogStore.hosting_domain ||
			$blogOriginalStore.hosting_url !== $blogStore.hosting_url
	);

	async function handleRevertToSubdomain() {
		const confirmed = await confirm({
			title: 'Revert to Subdomain Hosting',
			content: 'Are you sure you want to revert to subdomain hosting? This will change the URL of your blog back to your hyvorblogs.io subdomain.',
			confirmText: 'Revert',
			cancelText: 'Cancel',
			danger: true
		});

		if (!confirmed) {
			return;
		}

		const toastId = toast.loading('Reverting to subdomain hosting...');
		try {
			const updates = await updateHostedAt('subdomain');
			updateHostingInfoStore(updates);
			toast.success('Successfully reverted to subdomain hosting.', { id: toastId });
		} catch (err: any) {
			toast.error(err.message || 'Failed to revert to subdomain', { id: toastId });
		}
	}

	let activeHostingLabel = $derived(
		$blogStore.hosting_at === 'domain'
			? 'Custom Domain'
			: $blogStore.hosting_at === 'self'
				? 'Self-hosting'
				: 'Subdomain'
	);
	let activeHostingColor = $derived(
		$blogStore.hosting_at === 'domain'
			? 'blue'
			: $blogStore.hosting_at === 'self'
				? 'blue'
				: 'green'
	);

	onMount(() => {
		getHostingInfo()
			.then(res => {
				hostingInfoStore.set(res);
				isLoading = false;
			})
			.catch (err => {
				toast.error(err.message || 'Failed to load hosting information');
		});
	})
</script>

<DisabledOnTemp>
	{#if isLoading}
		<Loader block />
	{:else}
		<BlogSettingsSave
			keys={[
				'subdomain',
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

			<SplitControl label="Hosting configuration" caption="Where do you like to host your blog?">
				{#if $hostingInfoStore.hosting_at === 'subdomain'}
					{$blogStore.subdomain}.hyvorblogs.io
				{:else if $hostingInfoStore.hosting_at === 'domain'}
					{$blogStore.hosting_domain}
				{:else if $hostingInfoStore.hosting_at === 'self'}
					{$blogStore.hosting_url}
				{/if}
				&nbsp;
				<Tag color={activeHostingColor}>{activeHostingLabel}</Tag>

				<div class="hosting-config-wrap">
					<Button size="small" disabled={$hostingInfoStore.hosting_at === 'subdomain'} on:click={handleRevertToSubdomain}>Revert to Subdomain</Button>
					<Button size="small" disabled={$hostingInfoStore.hosting_at === 'domain'} on:click={() => showCustomDomainModal = true}>Set-up Custom Domain</Button>
					<Button size="small" disabled={$hostingInfoStore.hosting_at === 'self'} on:click={() => showSelfHostingModal = true}>Set-up Self-hosting</Button>
				</div>
			</SplitControl>

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

			<!-- TODO: Need to put this somewhere else (if needed) -->
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

		<SetupCustomDomainModal bind:show={showCustomDomainModal} />
		<SetupSelfHostingModal bind:show={showSelfHostingModal} />

	{/if}
</DisabledOnTemp>

<style>
	.hosting {
		padding: 20px 30px;
		height: 90%;
		overflow-y: auto;
	}
	.hosting-config-wrap {
		margin-top: 10px;
	}
</style>

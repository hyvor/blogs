<script lang="ts">
	import {
		Callout,
		confirm,
		FormControl,
		Loader,
		SplitControl,
		Switch,
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
	import type { Blog } from '../../../../lib/types';
	import { isSubdomainValid } from '../../../../lib/helper/isSubdomainValid';
	import { getHostingInfo, updateHostedAt } from './hostingActions';
	import SetupCustomDomainModal from './SetupCustomDomainModal.svelte';
	import SetupSelfHostingModal from './SetupSelfHostingModal.svelte';
	import HostingOption from './HostingOption.svelte';
	import { onMount } from 'svelte';

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

	async function handleRevertToSubdomain() {
		const confirmed = await confirm({
			title: 'Revert to Subdomain Hosting',
			content:
				'Are you sure you want to revert to subdomain hosting? This will change the URL of your blog back to your hyvorblogs.io subdomain.',
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

	onMount(() => {
		getHostingInfo()
			.then((res) => {
				hostingInfoStore.set(res);
				isLoading = false;
			})
			.catch((err) => {
				toast.error(err.message || 'Failed to load hosting information');
			});
	});
</script>

{#if isLoading}
	<Loader block />
{:else}
	<div class="hosting">
		<SplitControl label="Hosting Configuration" column>
			<div class="hosting-options">
				<HostingOption
					title="Subdomain"
					subtitle="Your blog will be hosted at its default subdomain, {$blogStore.subdomain}.hyvorblogs.io."
					active={$hostingInfoStore.hosting_at === 'subdomain'}
					buttonLabel="Revert to Subdomain"
					onclick={handleRevertToSubdomain}
				/>
				<HostingOption
					title="Custom Domain"
					subtitle="Your blog will be hosted at your own custom domain (e.g., blog.example.com)"
					active={$hostingInfoStore.hosting_at === 'domain'}
					buttonLabel="Setup Custom Domain"
					onclick={() => (showCustomDomainModal = true)}
					tag={$hostingInfoStore.custom_domain_setup?.status === 'pending'
						? { color: 'orange', label: 'Pending Verification' }
						: null}
				/>
				<HostingOption
					title="Self-Hosted"
					active={$hostingInfoStore.hosting_at === 'self'}
					buttonLabel="Setup Self-Hosting"
					onclick={() => (showSelfHostingModal = true)}
				>
					{#snippet subtitle()}
						You will serve your blog from your own server for <a
							class="hds-link"
							target="_blank"
							href="/docs/subdirectory">subdirectory hosting</a
						>
						or
						<a class="hds-link" target="_blank" href="/docs/headless">headless usage</a
						>.
					{/snippet}
				</HostingOption>
			</div>
		</SplitControl>

		<SplitControl
			label="Subdomain"
			caption="Unique subdomain for your blog. This will be used for subdomain hosting as well as in APIs to identify your blog."
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
	</div>

	<SetupCustomDomainModal bind:show={showCustomDomainModal} />
	<SetupSelfHostingModal bind:show={showSelfHostingModal} />
{/if}

<style>
	.hosting {
		padding: 20px 30px;
		height: 90%;
		overflow-y: auto;
	}
	.hosting-options {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 12px;
		width: 100%;
	}
</style>

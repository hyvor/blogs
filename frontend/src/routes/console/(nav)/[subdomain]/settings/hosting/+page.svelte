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
		blogStore,
		hostingInfoStore,
		updateHostingInfoStore
	} from '../../../../lib/stores/blogStore';
	import type { Blog } from '../../../../lib/types';
	import { isSubdomainValid } from '../../../../lib/helper/isSubdomainValid';
	import { getHostingInfo, updateHostedAt } from './hostingActions';
	import CreateCustomDomainModal from './CreateCustomDomainModal.svelte';
	import CustomDomainIntentModal from './CustomDomainIntentModal.svelte';
	import SetupSelfHostingModal from './SetupSelfHostingModal.svelte';
	import HostingOption from './HostingOption.svelte';
	import CustomDomainOption from './CustomDomainOption.svelte';
	import HostingChangeStatus from './HostingChangeStatus.svelte';
	import { onMount } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	const originalSubdomain = $blogStore.subdomain;
	let subdomain = $state($blogStore.subdomain);

	let subdomainError: null | string = $state(null);
	let showCreateCustomDomainModal = $state(false);
	let showCustomDomainIntentModal = $state(false);
	let showSelfHostingModal = $state(false);

	let isLoading = $state(true);

	let isHostingChangeInProgress = $derived(
		Boolean($hostingInfoStore.change && $hostingInfoStore.change.status === 'changing')
	);

	function handleRedirectSubdomainChange() {
		blogStore.update((b) => {
			return {
				...b,
				hosting_redirect_subdomain: !b.hosting_redirect_subdomain
			};
		});
	}

	function handleSubdomainInput(e: any) {
		const val = e.target.value;

		subdomainError = null;

		if (val === $blogStore.subdomain) {
			return;
		}

		subdomainError = isSubdomainValid(val);
	}

	async function handleRevertToSubdomain() {
		const confirmed = await confirm({
			title: i18n.t('console.settings.hosting.revertTitle'),
			content: i18n.t('console.settings.hosting.revertContent'),
			confirmText: i18n.t('console.settings.hosting.revert'),
			cancelText: i18n.t('console.common.cancel'),
			danger: true
		});

		if (!confirmed) {
			return;
		}

		const toastId = toast.loading(i18n.t('console.settings.hosting.reverting'));
		try {
			const updates = await updateHostedAt('subdomain');
			updateHostingInfoStore(updates);
			toast.success(i18n.t('console.settings.hosting.reverted'), { id: toastId });
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
		<SplitControl label={i18n.t('console.settings.hosting.configuration')} column>
			<div class="hosting-options">
				<HostingOption
					title={i18n.t('console.settings.hosting.subdomain')}
					subtitle="Your blog will be hosted at its default subdomain, {$blogStore.subdomain}.hyvorblogs.io."
					active={$hostingInfoStore.hosting_at === 'subdomain'}
					buttonLabel={i18n.t('console.settings.hosting.revertToSubdomain')}
					buttonDisabled={isHostingChangeInProgress}
					onclick={handleRevertToSubdomain}
				/>
				<CustomDomainOption
					disabled={isHostingChangeInProgress}
					onSetup={() => (showCreateCustomDomainModal = true)}
					onContinueSetup={() => (showCustomDomainIntentModal = true)}
					onConfigure={() => (showCreateCustomDomainModal = true)}
				/>
				<HostingOption
					title={i18n.t('console.settings.hosting.selfHosted')}
					active={$hostingInfoStore.hosting_at === 'self'}
					buttonLabel={i18n.t('console.settings.hosting.setupSelfHosting')}
					buttonDisabled={isHostingChangeInProgress}
					onclick={() => (showSelfHostingModal = true)}
				>
					{#snippet subtitle()}
						You will serve your blog from your own server for <a
							class="hds-link"
							target="_blank"
							href="/docs/subdirectory">subdirectory hosting</a
						>
						or
						<a class="hds-link" target="_blank" href="/docs/headless">headless usage</a>.
					{/snippet}
				</HostingOption>
			</div>
			{#if $hostingInfoStore.change}
				<HostingChangeStatus change={$hostingInfoStore.change} />
			{/if}
		</SplitControl>

		<SplitControl
			label={i18n.t('console.settings.hosting.subdomain')}
			caption={i18n.t('console.settings.hosting.subdomainCaption2')}
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
	</div>

	<CreateCustomDomainModal
		bind:show={showCreateCustomDomainModal}
		onSaved={() => (showCustomDomainIntentModal = true)}
	/>
	<CustomDomainIntentModal bind:show={showCustomDomainIntentModal} />
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
		grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
		gap: 12px;
		width: 100%;
	}

</style>

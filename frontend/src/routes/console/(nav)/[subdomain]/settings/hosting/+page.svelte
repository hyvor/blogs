<script lang="ts">
	import {
		Button,
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
	import { isSubdomainValid } from '../../../../lib/helper/isSubdomainValid';
	import { updateBlog } from '../../../../lib/actions/blogActions';
	import { getConfig } from '../../../../lib/config';
	import { getHostingInfo, updateHostedAt } from './hostingActions';
	import CreateCustomDomainModal from './CreateCustomDomainModal.svelte';
	import CustomDomainIntentModal from './CustomDomainIntentModal.svelte';
	import HostingUrl from './HostingUrl.svelte';
	import HostingHistoryModal from './HostingHistoryModal.svelte';
	import HostingOption from './HostingOption.svelte';
	import CustomDomainOption from './CustomDomainOption.svelte';
	import HostingChangeStatus from './HostingChangeStatus.svelte';
	import { onMount } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';
	import IconClockHistory from '@hyvor/icons/IconClockHistory';
	import { redirectIfCant } from '../../../../lib/scope.svelte';

	const i18n = getI18n();

	let subdomain = $state($blogStore.subdomain);

	let subdomainError: null | string = $state(null);
	let isSavingSubdomain = $state(false);
	let showCreateCustomDomainModal = $state(false);
	let showCustomDomainIntentModal = $state(false);
	let showHostingHistoryModal = $state(false);

	let isLoading = $state(true);

	let hasDeliveryDomain = Boolean(getConfig().domains.delivery);

	let isHostingChangeInProgress = $derived(
		Boolean($hostingInfoStore.change && $hostingInfoStore.change.status === 'changing')
	);

	async function handleRedirectSubdomainChange() {
		const newValue = !$blogStore.hosting_redirect_subdomain;
		const toastId = toast.loading('Updating...');
		try {
			await updateBlog({ hosting_redirect_subdomain: newValue });
			toast.info('Redirect subdomain setting updated', { id: toastId });
		} catch (err: any) {
			toast.error(err.message || 'Failed to update redirect subdomain setting', { id: toastId });
		}
	}

	function handleSubdomainInput(e: any) {
		const val = e.target.value;

		subdomainError = null;

		if (val === $blogStore.subdomain) {
			return;
		}

		subdomainError = isSubdomainValid(val);
	}

	async function handleSaveSubdomain() {
		const val = subdomain.trim();

		if (val === $blogStore.subdomain) {
			return;
		}

		const error = isSubdomainValid(val);
		if (error) {
			subdomainError = error;
			return;
		}

		isSavingSubdomain = true;
		const toastId = toast.loading('Updating subdomain...');
		try {
			const result = await updateHostedAt(val);
			updateHostingInfoStore(result);
			toast.info('Subdomain change started. It may take a moment to apply.', { id: toastId });
		} catch (err: any) {
			toast.error(err.message || 'Failed to update subdomain', { id: toastId });
		}
		isSavingSubdomain = false;
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
			const updates = await updateHostedAt();
			updateHostingInfoStore(updates);
			toast.info(i18n.t('console.settings.hosting.revertStarted'), {
				id: toastId
			});
		} catch (err: any) {
			toast.error(err.message || 'Failed to revert to subdomain', { id: toastId });
		}
	}

	onMount(() => {
		redirectIfCant('blog.write');
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
		<SplitControl column>
			{#snippet label()}
				<div class="hosting-config-label">
					<span class="title">{i18n.t('console.settings.hosting.configuration')}</span>
					<Button size="small" color="input" onclick={() => (showHostingHistoryModal = true)}>
						{#snippet start()}
							<IconClockHistory size={12} />
						{/snippet}
						{i18n.t('console.settings.hosting.history')}
					</Button>
				</div>
			{/snippet}

			<div class="hosting-options">
				<HostingOption
					title={hasDeliveryDomain
						? i18n.t('console.settings.hosting.subdomain')
						: i18n.t('console.settings.hosting.appSubdirectory')}
					subtitle={hasDeliveryDomain
						? i18n.t('console.settings.hosting.subdomainDesc', {
								subdomain: $blogStore.subdomain
							})
						: i18n.t('console.settings.hosting.appSubdirectoryDesc', {
								app: getConfig().domains.app,
								subdomain: $blogStore.subdomain
							})}
					active={$hostingInfoStore.hosting_at === 'subdomain'}
					buttonLabel={hasDeliveryDomain
						? i18n.t('console.settings.hosting.revertToSubdomain')
						: i18n.t('console.settings.hosting.revertToAppSubdirectory')}
					buttonDisabled={isHostingChangeInProgress}
					onclick={handleRevertToSubdomain}
				>
					{#snippet activeContent()}
						<div class="subdomain-editor">
							<FormControl>
								<TextInput
									bind:value={subdomain}
									block
									state={subdomainError ? 'error' : undefined}
									disabled={isHostingChangeInProgress || isSavingSubdomain}
									on:input={handleSubdomainInput}
								/>
								{#if subdomainError}
									<Validation state="error">{subdomainError}</Validation>
								{/if}
							</FormControl>
							<Button
								size="small"
								variant="outline"
								disabled={isHostingChangeInProgress ||
									isSavingSubdomain ||
									!!subdomainError ||
									subdomain === $blogStore.subdomain}
								onclick={handleSaveSubdomain}
							>
								Change Subdomain
							</Button>
						</div>
					{/snippet}
				</HostingOption>
				<CustomDomainOption
					disabled={isHostingChangeInProgress}
					onSetup={() => (showCreateCustomDomainModal = true)}
					onContinueSetup={() => (showCustomDomainIntentModal = true)}
					onConfigure={() => (showCreateCustomDomainModal = true)}
				/>
			</div>
			{#if $hostingInfoStore.change}
				<HostingChangeStatus change={$hostingInfoStore.change} />
			{/if}
		</SplitControl>

		<HostingUrl />

		{#if $blogStore.hosting_at !== 'subdomain' || $blogStore.hosting_url}
			<SplitControl
				label={i18n.t('console.settings.hosting.redirectSubdomain')}
				caption={i18n.t('console.settings.hosting.redirectSubdomainCaption', {
					subdomain: $blogStore.subdomain,
					target: $blogStore.hosting_url
						? i18n.t('console.settings.hosting.hostingUrlLower')
						: i18n.t('console.settings.hosting.customDomainLower')
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
	<HostingHistoryModal bind:show={showHostingHistoryModal} />
{/if}

<style>
	.hosting {
		padding: 20px 30px;
		height: 90%;
		overflow-y: auto;
	}
	.hosting-options {
		display: flex;
		flex-direction: column;
		gap: 12px;
		width: 100%;
	}
	.subdomain-editor {
		display: flex;
		align-items: flex-start;
		gap: 8px;
		margin-top: 16px;
	}
	.subdomain-editor :global(.form-control) {
		flex: 1;
	}
	.hosting-config-label {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
		width: 100%;
		margin-bottom: 6px;
	}
	.title {
		font-weight: 600;
	}
</style>

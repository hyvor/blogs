<script lang="ts">
	import { Loader, SplitControl, Switch, toast } from '@hyvor/design/components';
	import {
		blogStore,
		hostingInfoStore,
		updateHostingInfoStore
	} from '../../../../lib/stores/blogStore';
	import { updateBlog } from '../../../../lib/actions/blogActions';
	import { getHostingInfo } from './hostingActions';
	import Subdomain from './Subdomain.svelte';
	import CustomDomain from './CustomDomain.svelte';
	import HostingUrl from './HostingUrl.svelte';
	import CreateCustomDomainModal from './CreateCustomDomainModal.svelte';
	import CustomDomainIntentModal from './CustomDomainIntentModal.svelte';
	import { onMount } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';
	import { redirectIfCant } from '../../../../lib/scope.svelte';

	const i18n = getI18n();

	let showCreateCustomDomainModal = $state(false);
	let showCustomDomainIntentModal = $state(false);

	let isLoading = $state(true);

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

	function handleCustomDomainSaved() {
		if ($hostingInfoStore.custom_domain_intent) {
			// DNS verification is pending
			showCustomDomainIntentModal = true;
		} else {
			// bring-your-own certificates are connected right away
			toast.success(i18n.t('console.settings.hosting.customDomainConnected'));
		}
	}

	onMount(() => {
		redirectIfCant('blog.write');
		getHostingInfo()
			.then((res) => {
				updateHostingInfoStore(res);
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
		<Subdomain />

		<CustomDomain
			onSetup={() => (showCreateCustomDomainModal = true)}
			onContinueSetup={() => (showCustomDomainIntentModal = true)}
		/>

		<HostingUrl />

		{#if $hostingInfoStore.custom_domain || $blogStore.hosting_url}
			<SplitControl
				column
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
		onSaved={handleCustomDomainSaved}
	/>
	<CustomDomainIntentModal bind:show={showCustomDomainIntentModal} />
{/if}

<style>
	.hosting {
		padding: 20px 30px;
		height: 90%;
		overflow-y: auto;
	}
</style>

<script lang="ts">
	import { Button, SplitControl, Tag, Tooltip, confirm, toast } from '@hyvor/design/components';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import { hostingInfoStore, updateHostingInfoStore } from '../../../../lib/stores/blogStore';
	import { getI18n } from '../../../../lib/i18n';
	import { deleteCustomDomain } from './hostingActions';

	const i18n = getI18n();

	interface Props {
		onSetup: () => void;
		onContinueSetup: () => void;
	}

	let { onSetup, onContinueSetup }: Props = $props();

	let customDomain = $derived($hostingInfoStore.custom_domain);
	let intent = $derived($hostingInfoStore.custom_domain_intent);

	let removing = $state(false);

	function formatDate(timestamp: number): string {
		return new Date(timestamp * 1000).toLocaleDateString(undefined, {
			dateStyle: 'medium'
		});
	}

	async function handleRemove() {
		if (!customDomain) {
			return;
		}

		const confirmed = await confirm({
			title: i18n.t('console.settings.hosting.removeDomainTitle'),
			content: i18n.t('console.settings.hosting.removeDomainContent', {
				domain: customDomain.domain,
				url: $hostingInfoStore.subdomain_url
			}),
			confirmText: i18n.t('console.settings.hosting.removeDomain'),
			cancelText: i18n.t('console.common.cancel'),
			danger: true
		});

		if (!confirmed) {
			return;
		}

		removing = true;
		const toastId = toast.loading(i18n.t('console.settings.hosting.removingDomain'));

		try {
			const res = await deleteCustomDomain();
			updateHostingInfoStore(res);
			toast.success(i18n.t('console.settings.hosting.domainRemoved'), { id: toastId });
		} catch (err: any) {
			toast.error(err.message || 'Failed to remove custom domain', { id: toastId });
		} finally {
			removing = false;
		}
	}
</script>

<SplitControl
	column
	label={i18n.t('console.settings.hosting.optionDomain')}
	caption={i18n.t('console.settings.hosting.customDomainCaption')}
>
	{#if customDomain}
		<div class="domain">
			<div class="domain-name">
				<span>{customDomain.domain}</span>
				<Tag color="green" size="small">{i18n.t('console.settings.users.status.active')}</Tag>
			</div>
			<div class="actions">
				{#if !intent}
					<Button size="small" variant="outline" disabled={removing} on:click={onSetup}>
						{i18n.t('console.settings.hosting.changeDomain')}
					</Button>
				{/if}
				<Button
					size="small"
					variant="invisible"
					color="red"
					disabled={removing}
					on:click={handleRemove}
				>
					{i18n.t('console.settings.hosting.removeDomain')}
				</Button>
			</div>
		</div>
		<div class="tls-details">
			<div class="tls-row">
				<span class="tls-label">{i18n.t('console.settings.hosting.tlsCertificate')}</span>
				<span class="tls-value">
					{customDomain.tls_provider === 'auto'
						? i18n.t('console.settings.hosting.tlsAutomatic')
						: i18n.t('console.settings.hosting.tlsCustom')}
				</span>
			</div>
			{#if customDomain.valid_from && customDomain.valid_to}
				<div class="tls-row">
					<span class="tls-label">{i18n.t('console.settings.hosting.tlsValid')}</span>
					<span class="tls-value">
						{formatDate(customDomain.valid_from)} &ndash; {formatDate(customDomain.valid_to)}
						{#if customDomain.tls_provider === 'auto'}
							<Tooltip text={i18n.t('console.settings.hosting.tlsAutoRenew')}>
								<IconInfoCircle size={13} />
							</Tooltip>
						{/if}
					</span>
				</div>
			{/if}
		</div>
	{:else if !intent}
		<Button size="small" variant="outline" on:click={onSetup}>
			{i18n.t('console.settings.hosting.setupCustomDomain')}
		</Button>
	{/if}

	{#if intent}
		<div class="intent">
			<div class="note">
				{customDomain
					? i18n.t('console.settings.hosting.newDomainSetupInProgress')
					: i18n.t('console.settings.hosting.domainSetupInProgress')}
			</div>
			<div class="intent-domain">{intent.domain}</div>
			<div class="intent-button">
				<Button size="x-small" variant="outline" on:click={onContinueSetup}>
					{i18n.t('console.settings.hosting.continueSetup')}
				</Button>
			</div>
		</div>
	{/if}
</SplitControl>

<style>
	.domain {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
		flex-wrap: wrap;
	}
	.domain-name {
		display: flex;
		align-items: center;
		gap: 8px;
		min-width: 0;
		font-weight: 500;
		word-break: break-all;
	}
	.actions {
		display: flex;
		gap: 4px;
	}

	.tls-details {
		display: flex;
		flex-direction: column;
		gap: 6px;
		margin-top: 12px;
		padding-top: 12px;
		border-top: 1px solid var(--border);
	}
	.tls-row {
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 13px;
	}
	.tls-label {
		color: var(--text-light);
		min-width: 100px;
	}
	.tls-value {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		font-weight: 500;
	}

	.intent {
		padding: 10px 20px;
		margin-top: 15px;
		background-color: var(--blue-light);
		border-radius: 20px;
	}
	.intent .note {
		font-size: 14px;
		color: var(--text-light);
	}
	.intent-domain {
		font-size: 14px;
		font-weight: 600;
	}
	.intent-button {
		margin-top: 10px;
	}
</style>

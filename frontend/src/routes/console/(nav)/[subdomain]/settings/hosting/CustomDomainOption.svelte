<script lang="ts">
	import { Button, Tag, Tooltip } from '@hyvor/design/components';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import { hostingInfoStore } from '../../../../lib/stores/blogStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		disabled?: boolean;
		onSetup: () => void;
		onContinueSetup: () => void;
		onConfigure: () => void;
	}

	let { disabled = false, onSetup, onContinueSetup, onConfigure }: Props = $props();

	let customDomain = $derived($hostingInfoStore.custom_domain);
	let intent = $derived($hostingInfoStore.custom_domain_intent);
	let active = $derived($hostingInfoStore.hosting_at === 'domain');

	function formatDate(timestamp: number): string {
		return new Date(timestamp * 1000).toLocaleDateString(undefined, {
			dateStyle: 'medium'
		});
	}
</script>

<div class="hosting-option" class:active>
	<div class="hosting-option-header">
		<span class="hosting-option-title">{i18n.t('console.settings.hosting.optionDomain')}</span>
		{#if active}
			<Tag color="green" size="small">{i18n.t('console.settings.users.status.active')}</Tag>
		{/if}
	</div>

	<p class="hosting-option-subtitle">
		{i18n.t('console.settings.hosting.customDomainDesc')}
	</p>

	{#if !customDomain && !intent}
		<div class="button-wrap">
			<Button size="small" variant="outline" {disabled} on:click={onSetup}>
				{i18n.t('console.settings.hosting.setupCustomDomain')}
			</Button>
		</div>
	{:else}
		{#if customDomain}
			<div class="configured-domain">
				<div class="domain-row-info">
					<span class="domain-row-domain">{customDomain.domain}</span>
				</div>
				{#if !intent}
					<Button size="small" variant="outline" {disabled} on:click={onConfigure}>
						{i18n.t('console.settings.hosting.changeDomain')}
					</Button>
				{/if}
			</div>
			<div class="tls-details">
				<div class="tls-row">
					<span class="tls-label">TLS Certificate</span>
					<span class="tls-value">
						{customDomain.tls_provider === 'auto' ? 'Automatic' : 'Bring Your Own'}
					</span>
				</div>
				{#if customDomain.valid_from && customDomain.valid_to}
					<div class="tls-row">
						<span class="tls-label">Valid</span>
						<span class="tls-value">
							{formatDate(customDomain.valid_from)} &ndash; {formatDate(customDomain.valid_to)}
							{#if customDomain.tls_provider === 'auto'}
								<Tooltip
									text="Hyvor Blogs will automatically renew this certificate before it expires."
								>
									<IconInfoCircle size={13} />
								</Tooltip>
							{/if}
						</span>
					</div>
				{/if}
			</div>
		{/if}
		{#if intent}
			<div class="intent">
				<div class="note">
					{#if intent.has_certificate}
						{customDomain
							? i18n.t('console.settings.hosting.switchingToNewDomain')
							: i18n.t('console.settings.hosting.settingUpCustomDomain')}
					{:else if customDomain}
						{i18n.t('console.settings.hosting.newDomainSetupInProgress')}
					{:else}
						{i18n.t('console.settings.hosting.domainSetupInProgress')}
					{/if}
				</div>
				<div class="domain">
					{intent.domain}
				</div>
				{#if intent.has_certificate}
					<div class="note">{i18n.t('console.settings.hosting.waitingForHostingChange')}</div>
				{:else}
					<div class="button">
						<Button size="x-small" variant="outline" {disabled} on:click={onContinueSetup}>
							{i18n.t('console.settings.hosting.continueSetup')}
						</Button>
					</div>
				{/if}
			</div>
		{/if}
	{/if}
</div>

<style>
	.hosting-option {
		border: 2px solid var(--border);
		border-radius: var(--box-radius, 8px);
		padding: 16px;
		display: flex;
		flex-direction: column;
	}
	.hosting-option.active {
		border-color: var(--accent);
	}
	.hosting-option-header {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 6px;
	}
	.hosting-option-title {
		font-weight: 600;
		line-height: 1;
	}
	.hosting-option-subtitle {
		font-size: 14px;
		color: var(--text-light);
		margin: 0;
		flex: 1;
	}
	.button-wrap {
		margin-top: 16px;
	}
	.domain-rows {
		display: flex;
		flex-direction: column;
		gap: 10px;
	}
	.domain-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
	}
	.domain-row-info {
		display: flex;
		align-items: center;
		gap: 8px;
		min-width: 0;
	}
	.domain-row-domain {
		font-size: 14px;
		font-weight: 500;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.configured-domain {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
		margin-top: 16px;
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
	.intent .domain {
		font-size: 14px;
		font-weight: 600;
	}
	.intent .button {
		margin-top: 10px;
	}
</style>

<script lang="ts">
	import { Button, Tag } from '@hyvor/design/components';
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
</script>

<div class="hosting-option" class:active>
	<div class="hosting-option-header">
		<span class="hosting-option-title">{i18n.t('console.settings.hosting.optionDomain')}</span>
		{#if active && !intent}
			<Tag color="green" size="small">{i18n.t('console.settings.users.status.active')}</Tag>
		{/if}
	</div>

	{#if !customDomain && !intent}
		<p class="hosting-option-subtitle">
			{i18n.t('console.settings.hosting.customDomainDesc')}
		</p>
		<div class="button-wrap">
			<Button size="small" variant="outline" {disabled} on:click={onSetup}>
				{i18n.t('console.settings.hosting.setupCustomDomain')}
			</Button>
		</div>
	{:else}
		<div class="domain-rows">
			{#if customDomain}
				<div class="domain-row">
					<div class="domain-row-info">
						<span class="domain-row-domain">{customDomain.domain}</span>
						<Tag color="green" size="small">{i18n.t('console.settings.users.status.active')}</Tag>
					</div>
					<Button size="small" variant="outline" {disabled} on:click={onConfigure}>
						{i18n.t('console.settings.hosting.configureDomain')}
					</Button>
				</div>
			{/if}
			{#if intent}
				<div class="domain-row">
					<div class="domain-row-info">
						<span class="domain-row-domain">{intent.domain}</span>
						<Tag color="orange" size="small">Pending DNS Validation</Tag>
					</div>
					<Button size="small" variant="outline" {disabled} on:click={onContinueSetup}>
						{i18n.t('console.settings.hosting.continueSetup')}
					</Button>
				</div>
			{/if}
		</div>
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
</style>

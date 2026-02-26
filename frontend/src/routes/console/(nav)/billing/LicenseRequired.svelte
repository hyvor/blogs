<script lang="ts">
	import type { Snippet } from 'svelte';
	import type { License } from '../../lib/types';
	import IconArrowUpCircle from '@hyvor/icons/IconArrowUpCircle';
	import { Button } from '@hyvor/design/components';
	import { consoleUrl } from '../../lib/consoleUrl';
	import { resolvedLicenseStore } from '../../lib/stores';

	interface Props {
		licenseProperty?: keyof License;
		excludeTrial?: boolean;
		children: Snippet;
		upgradeText: Snippet;
	}

	let { licenseProperty, excludeTrial = false, children, upgradeText }: Props = $props();

	let hasLicense = $derived(licenseCheck());

	function licenseCheck() {
		const resolvedLicense = $resolvedLicenseStore;

		// not allowed in trial
		if (excludeTrial) {
			return resolvedLicense['type'] !== 'trial';
		}

		const license = resolvedLicense['license'];
		return (
			license && // sanity
			licenseProperty && // sanity
			license[licenseProperty] !== false && // feature not enabled
			license[licenseProperty] !== 0 // int feature (limit) is zero, so not enabled again
		);
	}
</script>

{#if hasLicense}
	{@render children()}
{:else}
	<div class="upgrade-required">
		<div class="upgrade-inner">
			<div class="upgrade-required-title">
				<IconArrowUpCircle />
				<span>Upgrade Required</span>
			</div>

			<div class="upgrade-required-content">
				{@render upgradeText()}
			</div>

			<div class="upgrade-cta">
				<Button as="a" href={consoleUrl('/billing')}>Upgrade Now</Button>
			</div>
		</div>
	</div>
{/if}

<style>
	.upgrade-required {
		padding: 20px;
		width: 100%;
		height: 100%;
		flex: 1;
	}
	.upgrade-inner {
		padding: 20px 25px;
		background-color: var(--blue-light);
		border-radius: 20px;
		width: 550px;
		max-width: 100%;
		margin: auto;
	}
	.upgrade-required-title {
		font-size: 1.2rem;
		font-weight: 600;
		text-align: center;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.upgrade-required-title :global(svg) {
		margin-right: 8px;
	}
	.upgrade-required-content {
		margin-top: 20px;
		text-align: center;
		line-height: 20px;
	}
	.upgrade-cta {
		margin-top: 20px;
		text-align: center;
	}
</style>

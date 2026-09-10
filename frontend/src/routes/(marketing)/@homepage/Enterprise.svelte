<script lang="ts">
	import { SpotlightSplit, GdprSeal, CcpaSeal, SsoSeal, IsoSeal } from '@hyvor/design/marketing';
	import { getMarketingI18n } from '../[[lang]]/marketingLang';

	const I18n = getMarketingI18n();
</script>

<SpotlightSplit
	title={I18n.t('homepage.enterprise.title')}
	description={I18n.t('homepage.enterprise.description')}
	stats={[I18n.t('homepage.enterprise.statUptime'), I18n.t('homepage.enterprise.statSupport')]}
	button={{
		href: 'https://hyvor.com/enterprise',
		label: I18n.t('homepage.enterprise.button'),
		external: true
	}}
	background="#574443"
>
	{#snippet content()}
		<div class="seals">
			<GdprSeal />
			<CcpaSeal />
			<SsoSeal />
			<IsoSeal />
		</div>
	{/snippet}
</SpotlightSplit>

<style>
	.seals {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
	}

	@media (max-width: 768px) {
		.seals {
			grid-template-columns: repeat(4, 1fr);
		}
	}

	@media (max-width: 480px) {
		.seals {
			grid-template-columns: repeat(2, 1fr);
		}
	}
</style>

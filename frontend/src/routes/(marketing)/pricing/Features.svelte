<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import FeatureStatus from './FeatureStatus.svelte';

	interface Feature {
		name: string;
		personal: boolean | string;
		starter: boolean | string;
		growth: boolean | string;
		premium: boolean | string;
		enterprise: boolean | string;
		tooltip: string;
	}

	interface FeatureGroup {
		category: string;
		features: Feature[];
	}

	const FEATURES: FeatureGroup[] = [
		{
			category: 'Basic Features',
			features: [
				{
					name: 'Blogs',
					personal: '1',
					starter: '3',
					growth: '10',
					premium: '20',
					enterprise: 'Custom',
					tooltip: 'Maximum number of blogs within your organization.'
				},
				{
					name: 'Users (team members)',
					personal: '1',
					starter: '5',
					growth: '15',
					premium: '50',
					enterprise: 'Custom',
					tooltip: 'Total number of users who write for your blog.'
				},
				{
					name: 'Media Storage',
					personal: '1GB',
					starter: '5GB',
					growth: '150GB',
					premium: '500GB',
					enterprise: 'Custom',
					tooltip: 'Total storage for uploaded media files (images, etc.).'
				},
				{
					name: 'Custom Themes',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: 'Use default themes for free or build your own custom theme.'
				},
				{
					name: 'Custom Domain',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: 'Host your blog on your own domain.'
				},
				{
					name: 'Multi-language Support',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: 'Add multiple languages to your blog and translate your posts.'
				},
				{
					name: 'No Branding',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: 'Remove Hyvor Blogs branding from your blog.'
				},
				{
					name: 'SEO Analysis',
					personal: false,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: 'In-post SEO analysis (check keywords, content, readability, etc.).'
				},
				{
					name: 'Link Analysis',
					personal: false,
					starter: false,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip:
						'Post link analysis, bi-weekly full-blog link analysis, and email reports.'
				}
			]
		},
		{
			category: 'AI Features',
			features: [
				{
					name: 'GPT Writing',
					personal: false,
					starter: false,
					growth: '100k tokens/m',
					premium: '1m tokens/m',
					enterprise: 'Custom',
					tooltip:
						'Use OpenAI GPT for content writing and keyword generation. ~1,000 tokens ≈ 750 words.'
				},
				{
					name: 'Auto-Translations',
					personal: false,
					starter: false,
					growth: '100k chars/m',
					premium: '500k chars/m',
					enterprise: 'Custom',
					tooltip:
						'Automatically translate your posts into multiple languages using DeepL.'
				}
			]
		},
		{
			category: 'Developer',
			features: [
				{
					name: 'Data API',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: 'Access public data of your blog via API.'
				},
				{
					name: 'Console API',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: 'The same API used in the Console — great for automation.'
				},
				{
					name: 'Delivery API',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: 'For self-serving a blog within web frameworks.'
				},
				{
					name: 'Webhooks',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: 'Receive an HTTP request on events in your blog.'
				}
			]
		},
		{
			category: 'Integrations',
			features: [
				{
					name: 'Hyvor Talk (Comments)',
					personal: '10k credits/m',
					starter: '25k credits/m',
					growth: '100k credits/m',
					premium: '250k credits/m',
					enterprise: 'Custom',
					tooltip:
						'Add Hyvor Talk commenting system for free. Credits are used per comment load.'
				},
				{
					name: 'Hyvor Post (Newsletter)',
					personal: '5k emails/m',
					starter: '15k emails/m',
					growth: '50k emails/m',
					premium: '150k emails/m',
					enterprise: 'Custom',
					tooltip:
						'Add Hyvor Post newsletter system for free. Includes a complimentary license for Hyvor Post.'
				}
			]
		},
		{
			category: 'Enterprise',
			features: [
				{
					name: 'SSO & SAML',
					personal: false,
					starter: false,
					growth: false,
					premium: false,
					enterprise: true,
					tooltip: 'Single sign-on via SAML for your organization’s identity provider.'
				},
				{
					name: 'GDPR, CCPA & ISO Compliance',
					personal: false,
					starter: false,
					growth: false,
					premium: false,
					enterprise: true,
					tooltip: 'Formal compliance documentation and DPAs for regulated organizations.'
				},
				{
					name: 'Priority Support',
					personal: false,
					starter: false,
					growth: false,
					premium: false,
					enterprise: true,
					tooltip: 'Dedicated, faster-response support for your team.'
				},
				{
					name: 'Custom Contract & SLA',
					personal: false,
					starter: false,
					growth: false,
					premium: false,
					enterprise: true,
					tooltip: 'Custom-tailored contract terms and a service-level agreement.'
				}
			]
		}
	];
</script>

<div class="title-wrap hds-container">
	<div class="eyebrow">Compare</div>
	<h2>All features, plan by plan</h2>
	<p class="subtitle">A detailed breakdown of everything included in each plan.</p>
</div>

<div class="table-outer hds-container-max">
	<div class="table-wrap hds-box">
		<div class="plan-names">
			<div class="col-name"></div>
			<div class="col">Personal</div>
			<div class="col">Starter</div>
			<div class="col popular">
				<span>Growth</span><span class="popular-tag">Most Popular</span>
			</div>
			<div class="col">Premium</div>
			<div class="col">Enterprise</div>
		</div>

		{#each FEATURES as { category, features }, i}
			<div class="category-block" class:last={i === FEATURES.length - 1}>
				<div class="category-name">
					<div class="col-name">{category}</div>
					<div class="col"></div>
					<div class="col"></div>
					<div class="col popular"></div>
					<div class="col"></div>
					<div class="col"></div>
				</div>
				{#each features as feature}
					<div class="feature-row">
						<div class="col-name">
							{feature.name}&nbsp;
							<Tooltip text={feature.tooltip} position="right">
								<IconInfoCircle />
							</Tooltip>
						</div>
						<div class="col"><FeatureStatus status={feature.personal} /></div>
						<div class="col"><FeatureStatus status={feature.starter} /></div>
						<div class="col popular"><FeatureStatus status={feature.growth} /></div>
						<div class="col"><FeatureStatus status={feature.premium} /></div>
						<div class="col"><FeatureStatus status={feature.enterprise} /></div>
					</div>
				{/each}
			</div>
		{/each}
	</div>
</div>

<style lang="scss">
	.title-wrap {
		padding-top: 100px;
		text-align: center;
	}

	.eyebrow {
		display: inline-block;
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0.1em;
		text-transform: uppercase;
		color: var(--accent);
		margin-bottom: 14px;
	}

	h2 {
		font-family: var(--font-serif);
		font-size: 32px;
		font-weight: 700;
		letter-spacing: -0.01em;
		margin: 0 0 10px;
	}

	.subtitle {
		font-size: 16px;
		color: var(--text-light);
		margin: 0;
	}

	.table-outer {
		margin-top: 44px;
	}

	.table-wrap {
		width: 1400px;
		max-width: 100%;
		margin: auto;
		overflow-x: auto;
		padding: 30px 30px 10px;
		border: 1px solid var(--border);
	}

	.col-name {
		flex: 1.4;
		min-width: 190px;
	}

	.col {
		flex: 1;
		min-width: 110px;
		text-align: center;
	}

	.plan-names {
		display: flex;
		align-items: center;
		font-size: 16px;
		font-weight: 700;
		background-color: var(--box-background);
		padding-bottom: 12px;
		border-bottom: 1px solid var(--border);

		.col-name {
			visibility: hidden;
		}

		.col {
			position: relative;
		}

		.col.popular {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			color: var(--accent);
			background: var(--accent-light-mid);
			border-radius: 12px 12px 0 0;
			padding: 8px 6px;
			margin: -8px 0 0;
		}
	}

	.popular-tag {
		display: inline-flex;
		font-size: 10px;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: var(--accent-text);
		background: var(--accent);
		border-radius: 100px;
		padding: 2px 8px;
		white-space: nowrap;
	}

	.category-block {
		border-bottom: 1px solid var(--border);
		&.last {
			border-bottom: none;
		}
	}

	.category-name {
		display: flex;
		align-items: center;

		.col-name {
			font-weight: 700;
			font-size: 14px;
			text-transform: uppercase;
			letter-spacing: 0.03em;
			color: var(--text-light);
			padding-top: 26px;
			padding-bottom: 8px;
		}
	}

	.feature-row {
		display: flex;
		align-items: stretch;

		.col-name {
			cursor: default;
			display: inline-flex;
			align-items: center;
			gap: 3px;
			padding: 11px 0;
		}

		.col {
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 11px 0;
		}

		&:hover {
			background-color: var(--hover);
		}
	}

	.col.popular {
		background-color: var(--accent-light-mid);
	}

	@media (max-width: 640px) {
		.table-wrap {
			padding: 24px 20px 10px;
		}
	}
</style>

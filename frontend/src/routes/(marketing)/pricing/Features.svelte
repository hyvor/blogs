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
					tooltip: 'Maximum number of blogs within your organization.'
				},
				{
					name: 'Users (team members)',
					personal: '1',
					starter: '5',
					growth: '15',
					premium: '50',
					tooltip: 'Total number of users who write for your blog.'
				},
				{
					name: 'Media Storage',
					personal: '1GB',
					starter: '5GB',
					growth: '150GB',
					premium: '500GB',
					tooltip: 'Total storage for uploaded media files (images, etc.).'
				},
				{
					name: 'Custom Themes',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					tooltip: 'Use default themes for free or build your own custom theme.'
				},
				{
					name: 'Custom Domain',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					tooltip: 'Host your blog on your own domain.'
				},
				{
					name: 'Multi-language Support',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					tooltip: 'Add multiple languages to your blog and translate your posts.'
				},
				{
					name: 'No Branding',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					tooltip: 'Remove Hyvor Blogs branding from your blog.'
				},
				{
					name: 'SEO Analysis',
					personal: false,
					starter: true,
					growth: true,
					premium: true,
					tooltip: 'In-post SEO analysis (check keywords, content, readability, etc.).'
				},
				{
					name: 'Link Analysis',
					personal: false,
					starter: false,
					growth: true,
					premium: true,
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
					tooltip:
						'Use OpenAI GPT for content writing and keyword generation. ~1,000 tokens ≈ 750 words.'
				},
				{
					name: 'Auto-Translations',
					personal: false,
					starter: false,
					growth: '100k chars/m',
					premium: '500k chars/m',
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
					tooltip: 'Access public data of your blog via API.'
				},
				{
					name: 'Console API',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					tooltip: 'The same API used in the Console — great for automation.'
				},
				{
					name: 'Delivery API',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					tooltip: 'For self-serving a blog within web frameworks.'
				},
				{
					name: 'Webhooks',
					personal: true,
					starter: true,
					growth: true,
					premium: true,
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
					tooltip:
						'Add Hyvor Talk commenting system for free. Credits are used per comment load.'
				},
				{
					name: 'Hyvor Post (Newsletter)',
					personal: '5k emails/m',
					starter: '15k emails/m',
					growth: '50k emails/m',
					premium: '150k emails/m',
					tooltip:
						'Add Hyvor Post newsletter system for free. Includes a complimentary license for Hyvor Post.'
				}
			]
		}
	];
</script>

<div class="title-wrap hds-container">
	<h2>Features</h2>
</div>

<div class="table-wrap">
	<div class="plan-names">
		<div></div>
		<div>Personal</div>
		<div>Starter</div>
		<div>Growth</div>
		<div>Premium</div>
	</div>

	{#each FEATURES as { category, features }}
		<div class="category-name">{category}</div>
		{#each features as feature}
			<div class="feature">
				<div class="name">
					{feature.name}&nbsp;
					<Tooltip text={feature.tooltip} position="right">
						<IconInfoCircle />
					</Tooltip>
				</div>
				<div><FeatureStatus status={feature.personal} /></div>
				<div><FeatureStatus status={feature.starter} /></div>
				<div><FeatureStatus status={feature.growth} /></div>
				<div><FeatureStatus status={feature.premium} /></div>
			</div>
		{/each}
	{/each}
</div>

<style lang="scss">
	.title-wrap {
		padding-top: 40px;
	}

	h2 {
		font-size: 30px;
		font-weight: 600;
		margin-bottom: 10px;
	}

	.table-wrap {
		width: 1300px;
		max-width: 100%;
		margin: auto;
		overflow-x: auto;
		padding: 0 40px;
	}

	.plan-names {
		display: flex;
		align-items: center;
		font-size: 18px;
		font-weight: 600;
		margin-top: 30px;
		position: sticky;
		top: var(--header-height);
		gap: 10px;
		div {
			flex: 1;
			min-width: 140px;
			background-color: var(--box-background);
			box-shadow: var(--box-shadow);
			border-radius: var(--box-radius);
			padding: 15px 25px;
			&:first-child {
				visibility: hidden;
			}
		}
	}

	.category-name {
		font-weight: 600;
		margin-top: 30px;
		margin-bottom: 10px;
		font-size: 1.1rem;
	}

	.feature {
		display: flex;
		padding: 13px 0;
		.name {
			cursor: default;
			min-width: 140px;
			display: inline-flex;
			align-items: center;
			gap: 3px;
		}
		div {
			flex: 1;
			min-width: 140px;
		}
	}
</style>

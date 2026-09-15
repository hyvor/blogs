<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconGithub from '@hyvor/icons/IconGithub';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import TerminalMockup from '../../@components/TerminalMockup.svelte';
	import { getMarketingI18n } from '../marketingLang';

	const I18n = getMarketingI18n();

	const bullets = $derived([
		I18n.t('pricing.selfHost.bulletFree'),
		I18n.t('pricing.selfHost.bulletDocker'),
		I18n.t('pricing.selfHost.bulletOwnership'),
		I18n.t('pricing.selfHost.bulletNoLockIn')
	]);
</script>

<section class="self-host hds-container">
	<div class="inner">
		<div class="text-col">
			<span class="eyebrow">{I18n.t('pricing.selfHost.eyebrow')}</span>
			<h2>{I18n.t('pricing.selfHost.title')}</h2>
			<p>
				{I18n.t('pricing.selfHost.description')}
			</p>

			<ul class="bullets">
				{#each bullets as b}
					<li><IconCheckCircleFill size={14} />{b}</li>
				{/each}
			</ul>

			<div class="buttons">
				<Button as="a" href="/hosting" size="medium" variant="fill" color="accent">
					{I18n.t('pricing.selfHost.buttonLearn')}
				</Button>
				<span class="github-btn">
					<Button
						as="a"
						href="https://github.com/hyvor/blogs"
						target="_blank"
						rel="noopener"
						size="medium"
						variant="outline"
						color="gray"
					>
						{#snippet start()}<IconGithub size={14} />{/snippet}
						{I18n.t('pricing.selfHost.buttonGithub')}
						{#snippet end()}<IconBoxArrowUpRight size={11} />{/snippet}
					</Button>
				</span>
			</div>
		</div>

		<div class="visual-col">
			<TerminalMockup output="Hyvor Blogs running at https://acme.com/blog" />
		</div>
	</div>
</section>

<style>
	.self-host {
		margin-top: 60px;
	}

	.inner {
		display: flex;
		align-items: center;
		gap: 60px;
		padding: 50px 55px;
		border-radius: var(--box-radius);
		box-shadow: var(--box-shadow);
		background: linear-gradient(135deg, var(--accent-light-mid), var(--box-background) 60%);
	}

	.text-col,
	.visual-col {
		flex: 1;
		min-width: 0;
	}

	.eyebrow {
		display: inline-block;
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0.1em;
		text-transform: uppercase;
		color: var(--accent);
		margin-bottom: 16px;
	}

	h2 {
		font-family: var(--font-serif);
		font-weight: 700;
		font-size: clamp(24px, 2.6vw, 30px);
		letter-spacing: -0.01em;
		line-height: 1.2;
		margin: 0 0 16px;
	}

	p {
		font-size: 15px;
		line-height: 1.6;
		color: var(--text-light);
		margin: 0 0 28px;
		max-width: 440px;
	}

	.bullets {
		list-style: none;
		margin: 0 0 32px;
		padding: 0;
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.bullets li {
		display: flex;
		align-items: center;
		gap: 10px;
		font-size: 14px;
	}

	.bullets li :global(svg) {
		color: var(--accent);
		flex-shrink: 0;
	}

	.buttons {
		display: flex;
		align-items: center;
		gap: 12px;
		flex-wrap: wrap;
	}

	.github-btn :global(.slot.start) {
		margin-right: 10px;
	}

	.visual-col {
		display: flex;
		justify-content: center;
	}

	@media (max-width: 900px) {
		.inner {
			flex-direction: column;
			align-items: stretch;
			gap: 40px;
			padding: 36px 28px;
			text-align: center;
		}

		p {
			max-width: none;
			margin-left: auto;
			margin-right: auto;
		}

		.bullets {
			align-items: center;
		}

		.buttons {
			justify-content: center;
			flex-wrap: wrap;
		}

		.visual-col {
			width: 100%;
		}
	}
</style>

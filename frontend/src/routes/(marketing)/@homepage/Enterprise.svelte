<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';

	const seals = [
		{ label: 'GDPR', pending: false },
		{ label: 'CCPA', pending: false },
		{ label: 'SSO', pending: false },
		{ label: 'ISO', pending: true }
	];
</script>

<section class="enterprise">
	<div class="hds-container inner">
		<div class="text-side">
			<h2>Enterprise-ready.</h2>
			<p class="description">
				Enterprise-grade security and compliance, without adding complexity to your
				publishing workflow.
			</p>

			<div class="stat-row">
				<span class="stat">&gt; 99.9% Uptime</span>
				<span class="stat">Priority Support</span>
			</div>

			<div class="buttons">
				<Button
					as="a"
					href="https://hyvor.com/enterprise"
					target="_blank"
					variant="outline"
					color="input"
				>
					Contact Sales
					{#snippet end()}<IconBoxArrowUpRight size={11} />{/snippet}
				</Button>
			</div>
		</div>

		<div class="seals">
			{#each seals as s}
				<div class="seal" class:pending={s.pending}>
					<span class="seal-label">{s.label}</span>
					{#if s.pending}<span class="seal-note">Soon</span>{/if}
				</div>
			{/each}
		</div>
	</div>
</section>

<style>
	.enterprise {
		background: #574443;
		padding: 96px 0;
	}

	.inner {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 48px;
	}

	.text-side {
		flex: 1;
		min-width: 0;
	}

	.label {
		font-size: 14px;
		font-weight: 600;
		letter-spacing: 0.07em;
		text-transform: uppercase;
		color: var(--accent-light-mid);
		margin: 0 0 20px;
	}

	h2 {
		font-size: clamp(30px, 4vw, 44px);
		font-weight: 800;
		letter-spacing: -0.02em;
		line-height: 1.1;
		color: #fff;
		margin: 0 0 18px;
	}

	.description {
		font-size: 16px;
		line-height: 1.7;
		color: rgba(255, 255, 255, 0.7);
		max-width: 460px;
		margin: 0 0 28px;
	}

	.stat-row {
		display: flex;
		flex-wrap: wrap;
		gap: 10px 20px;
		margin-bottom: 28px;
	}

	.stat {
		font-size: 14px;
		font-weight: 600;
		color: rgba(255, 255, 255, 0.85);
	}

	.buttons {
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
	}

	.buttons :global(.button) {
		border-color: rgba(255, 255, 255, 0.3) !important;
		color: rgba(255, 255, 255, 0.85) !important;
		background: transparent !important;
	}

	.buttons :global(.button):hover {
		border-color: rgba(255, 255, 255, 0.55) !important;
		color: #fff !important;
	}

	.seals {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
		flex-shrink: 0;
	}

	.seal {
		position: relative;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 100px;
		height: 100px;
		border-radius: 100px;
		border: 1.5px solid rgba(255, 255, 255, 0.3);
	}

	.seal-label {
		font-size: 13px;
		font-weight: 700;
		letter-spacing: 0.03em;
		color: #fff;
	}

	.seal.pending {
		border-style: dashed;
		border-color: rgba(255, 255, 255, 0.2);
	}

	.seal.pending .seal-label {
		color: rgba(255, 255, 255, 0.5);
	}

	.seal-note {
		position: absolute;
		bottom: -10px;
		font-size: 10px;
		font-weight: 600;
		padding: 2px 8px;
		border-radius: 100px;
		color: rgba(255, 255, 255, 0.6);
		background: rgba(255, 255, 255, 0.1);
		white-space: nowrap;
	}

	@media (max-width: 768px) {
		.inner {
			flex-direction: column;
			text-align: center;
			gap: 36px;
		}

		.description {
			max-width: 100%;
		}

		.stat-row,
		.buttons {
			justify-content: center;
		}

		.seals {
			grid-template-columns: repeat(4, 1fr);
		}

		.seal {
			width: 76px;
			height: 76px;
		}
	}

	@media (max-width: 480px) {
		.seals {
			grid-template-columns: repeat(2, 1fr);
		}
	}
</style>

<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';

	interface Props {
		title?: string;
		[key: string]: any;
	}

	let { title = 'Start your blog today', ...rest }: Props = $props();

	const checks = ['14-day free trial', 'No credit card required', 'Cancel anytime'];
</script>

<section class="cta-section" {...rest}>
	<div class="hds-container inner">
		<div class="badge">Open-source &amp; free to try</div>
		<h2>{title}</h2>
		<p>Join hundreds of bloggers and businesses building with Hyvor Blogs.</p>

		<div class="cta-actions">
			<Button as="a" size="large" href="/console?signup">Start your blog &rarr;</Button>
		</div>

		<div class="checks">
			{#each checks as check}
				<div class="check">
					<IconCheckCircleFill size={15} />
					{check}
				</div>
			{/each}
		</div>
	</div>
</section>

<style lang="scss">
	.cta-section {
		padding: 96px 0 138px;
		background: linear-gradient(
			to bottom,
			var(--background),
			color-mix(in srgb, var(--accent) 40%, var(--background))
		);
		text-align: center;
	}

	.inner {
		width: 600px;
		max-width: 100%;
	}

	.badge {
		display: inline-flex;
		padding: 4px 14px;
		border-radius: 100px;
		font-size: 13px;
		font-weight: 600;
		background: color-mix(in srgb, var(--accent) 12%, transparent);
		color: var(--accent);
		border: 1px solid color-mix(in srgb, var(--accent) 25%, transparent);
		margin-bottom: 24px;
	}

	h2 {
		font-size: clamp(28px, 4vw, 40px);
		font-weight: 800;
		margin: 0 0 12px;
		letter-spacing: -0.02em;
	}

	p {
		font-size: 1rem;
		color: var(--text-light);
		margin: 0 0 32px;
	}

	.cta-actions {
		margin-bottom: 24px;
	}

	.checks {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 20px;
		flex-wrap: wrap;
	}

	.check {
		display: flex;
		align-items: center;
		gap: 6px;
		font-size: 14px;
		color: var(--text-light);

		:global(svg) {
			color: var(--accent);
		}
	}
</style>

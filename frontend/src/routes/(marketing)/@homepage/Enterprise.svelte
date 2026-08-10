<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import GdprSeal from './Seals/GdprSeal.svelte';
	import CcpaSeal from './Seals/CcpaSeal.svelte';
	import SsoSeal from './Seals/SsoSeal.svelte';
	import IsoSeal from './Seals/IsoSeal.svelte';

	let mouseX = $state(0);
	let mouseY = $state(0);
	let hovering = $state(false);

	function handlePointerMove(event: PointerEvent) {
		const bounds = (event.currentTarget as HTMLElement).getBoundingClientRect();
		mouseX = event.clientX - bounds.left;
		mouseY = event.clientY - bounds.top;
	}
</script>

<!-- svelte-ignore a11y_no_static_element_interactions -->
<section
	class="enterprise"
	onpointermove={handlePointerMove}
	onpointerenter={() => (hovering = true)}
	onpointerleave={() => (hovering = false)}
	style="--mx: {mouseX}px; --my: {mouseY}px;"
>
	<svg class="bg-pattern" width="100%" height="100%" aria-hidden="true">
		<defs>
			<pattern id="enterprise-grid" width="56" height="56" patternUnits="userSpaceOnUse">
				<path d="M 56 0 L 0 0 0 56" fill="none" stroke="white" stroke-width="1" />
			</pattern>
			<radialGradient id="enterprise-fade" cx="50%" cy="40%" r="75%">
				<stop offset="0%" stop-color="white" stop-opacity="1" />
				<stop offset="100%" stop-color="white" stop-opacity="0" />
			</radialGradient>
			<mask id="enterprise-mask">
				<rect width="100%" height="100%" fill="url(#enterprise-fade)" />
			</mask>
		</defs>
		<rect
			width="100%"
			height="100%"
			fill="url(#enterprise-grid)"
			mask="url(#enterprise-mask)"
		/>
	</svg>

	<div class="grid-highlight" class:visible={hovering} aria-hidden="true"></div>

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
			<GdprSeal />
			<CcpaSeal />
			<SsoSeal />
			<IsoSeal />
		</div>
	</div>
</section>

<style>
	.enterprise {
		position: relative;
		background: #574443;
		padding: 96px 0;
		overflow: hidden;
	}

	.bg-pattern {
		position: absolute;
		inset: 0;
		opacity: 0.08;
		pointer-events: none;
	}

	.grid-highlight {
		position: absolute;
		inset: 0;
		pointer-events: none;
		background-image:
			linear-gradient(to right, rgba(255, 238, 217, 0.9) 1px, transparent 1px),
			linear-gradient(to bottom, rgba(255, 238, 217, 0.9) 1px, transparent 1px);
		background-size: 56px 56px;
		-webkit-mask-image: radial-gradient(
			180px circle at var(--mx, 50%) var(--my, 50%),
			black 0%,
			black 35%,
			transparent 75%
		);
		mask-image: radial-gradient(
			180px circle at var(--mx, 50%) var(--my, 50%),
			black 0%,
			black 35%,
			transparent 75%
		);
		opacity: 0;
		transition: opacity 0.35s ease;
	}

	.grid-highlight.visible {
		opacity: 0.2;
	}

	.inner {
		position: relative;
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
	}

	@media (max-width: 480px) {
		.seals {
			grid-template-columns: repeat(2, 1fr);
		}
	}
</style>

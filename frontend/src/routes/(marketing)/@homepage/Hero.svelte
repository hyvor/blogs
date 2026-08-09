<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
</script>

<section class="hero">
	<svg class="hero-pattern" aria-hidden="true" focusable="false">
		<defs>
			<pattern id="hero-dot-pattern" width="28" height="28" patternUnits="userSpaceOnUse">
				<circle cx="2" cy="2" r="1.6" fill="currentColor" />
			</pattern>
		</defs>
		<rect width="100%" height="100%" fill="url(#hero-dot-pattern)" />
	</svg>

	<div class="hds-container inner">
		<div class="left">
			<!-- <div class="badge">Open-source</div> -->

			<h1 class="anim anim-1">
				You Publish.<br /><span class="heading-small">(We'll handle the rest)</span>
			</h1>

			<h2 class="subtitle anim anim-2">
				Hyvor Blogs is our <span class="marker">blogging platform</span>. It's
				<span class="hl">fast</span>, <span class="hl">simple</span>, and
				<span class="hl">open-source</span>!
			</h2>

			<div class="buttons anim anim-3">
				<Button as="a" href="/console?signup" size="x-large">Start your blog</Button>
			</div>
		</div>
	</div>

	<div class="scroll-indicator anim anim-4" aria-hidden="true">
		<div class="scroll-mouse">
			<div class="scroll-dot"></div>
		</div>
		<IconChevronDown size={12} />
	</div>
</section>

<style>
	.hero {
		position: relative;
		display: flex;
		align-items: center;
		min-height: calc(100vh - var(--header-height, 60px));
		padding: 60px 0 80px;
		overflow: hidden;
	}

	.hero-pattern {
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
		color: var(--border);
		mask-image: radial-gradient(ellipse 65% 60% at 50% 38%, black 0%, transparent 75%);
		-webkit-mask-image: radial-gradient(ellipse 65% 60% at 50% 38%, black 0%, transparent 75%);
		pointer-events: none;
	}

	.inner {
		position: relative;
		z-index: 1;
		display: flex;
		align-items: center;
		gap: 100px;
		width: 1120px;
		max-width: 100%;
	}

	.left {
		flex: 1;
		min-width: 0;
		text-align: center;
	}
	/* 
	.badge {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 4px 12px;
		border-radius: 100px;
		font-size: 13px;
		font-weight: 600;
		background: color-mix(in srgb, var(--accent) 12%, transparent);
		color: var(--accent);
		margin-bottom: 24px;
		border: 1px solid color-mix(in srgb, var(--accent) 25%, transparent);
	} */

	h1 {
		margin: 0;
		font-size: clamp(36px, 5vw, 56px);
		line-height: 1.1;
		letter-spacing: -0.02em;
		font-weight: 800;
		font-family: var(--font-serif);
	}

	/* smaller fontsize */
	.heading-small {
		font-size: clamp(35px, 5vw, 46px);
		font-weight: 800;
	}

	h2 {
		font-size: clamp(26px, 3.5vw, 38px);
		font-weight: 500;
		letter-spacing: -0.02em;
		line-height: 1.15;
		margin: 0 0 16px;
		font-family: var(--font-serif);
	}

	.subtitle {
		margin: auto;
		margin-top: 30px;
		font-size: clamp(22px, 3vw, 26px);
		line-height: 1.6;
		color: color-mix(in srgb, var(--text) 70%, var(--text-light));
		width: 480px;
		max-width: 100%;
	}

	.hl {
		color: var(--accent);
		font-weight: 700;
	}

	.marker {
		position: relative;
		font-weight: 700;
		background-image: linear-gradient(
			100deg,
			color-mix(in srgb, var(--accent) 35%, transparent) 0%,
			color-mix(in srgb, var(--accent) 45%, transparent) 100%
		);
		background-repeat: no-repeat;
		background-size: 100% 0.5em;
		background-position: 0 88%;
		box-decoration-break: clone;
		-webkit-box-decoration-break: clone;
	}

	.buttons {
		display: flex;
		gap: 12px;
		margin-top: 36px;
		flex-wrap: wrap;
		justify-content: center;
	}

	/* Entrance animation */
	.anim {
		opacity: 0;
		animation: hero-fade-up 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
	}

	.anim-1 {
		animation-delay: 0.05s;
	}

	.anim-2 {
		animation-delay: 0.15s;
	}

	.anim-3 {
		animation-delay: 0.25s;
	}

	.anim-4 {
		animation-delay: 0.6s;
	}

	@keyframes hero-fade-up {
		from {
			opacity: 0;
			transform: translateY(18px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.anim {
			animation: none;
			opacity: 1;
			transform: none;
		}
	}

	/* Scroll indicator */
	.scroll-indicator {
		position: absolute;
		left: 50%;
		bottom: 28px;
		transform: translateX(-50%);
		z-index: 1;
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 8px;
		color: var(--text-light);
	}

	.scroll-mouse {
		width: 24px;
		height: 38px;
		border: 2px solid var(--border);
		border-radius: 100px;
		display: flex;
		justify-content: center;
		padding-top: 6px;
	}

	.scroll-dot {
		width: 4px;
		height: 8px;
		border-radius: 2px;
		background: var(--accent);
		animation: scroll-wheel 1.8s ease-in-out infinite;
	}

	@keyframes scroll-wheel {
		0% {
			opacity: 1;
			transform: translateY(0);
		}
		60% {
			opacity: 0;
			transform: translateY(12px);
		}
		100% {
			opacity: 0;
			transform: translateY(12px);
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.scroll-dot {
			animation: none;
		}
	}

	/* Responsive */
	@media (max-width: 992px) {
		.hero {
			min-height: unset;
			padding: 60px 0;
		}

		.inner {
			flex-direction: column;
			gap: 48px;
			text-align: center;
		}

		.subtitle {
			max-width: 100%;
		}

		.buttons {
			justify-content: center;
		}

		.trial-checks {
			display: flex;
			justify-content: center;
		}

		.right {
			width: 100%;
			max-width: 560px;
			margin: 0 auto;
		}

		.scroll-indicator {
			display: none;
		}
	}
</style>

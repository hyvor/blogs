<script lang="ts">
	let videoEl: HTMLVideoElement | undefined = $state();
	$effect(() => {
		const el = videoEl;
		if (!el) return;
		const io = new IntersectionObserver(
			([entry]) => {
				if (entry?.isIntersecting) el.play().catch(() => {});
				else el.pause();
			},
			{ threshold: 0.2 }
		);
		io.observe(el);
		return () => io.disconnect();
	});
</script>

<div class="ai-screencast">
	<!-- <video bind:this={videoEl} muted loop playsinline preload="metadata">
		<source src="/images/homepage/ai-screencast.mp4" type="video/mp4" />
		<track kind="captions" />
	</video> -->

	<div class="fade-overlay" aria-hidden="true"></div>
</div>

<style>
	.ai-screencast {
		--left-pull: 380px;
		--right-bleed: calc(max(0px, (100vw - 1000px) / 2) + 72px);
		position: relative;
		z-index: 1;
		margin-top: 64px;
		margin-left: calc(-1 * var(--left-pull));
		height: 620px;
		width: calc(100% + var(--left-pull) + var(--right-bleed));
		border-radius: 20px 0 0 20px;
		border: 1px solid var(--border);
		border-right: none;
		overflow: hidden;
		background: var(--background);
		box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent);
		transition: box-shadow 0.4s ease;
	}
	.ai-screencast:hover {
		z-index: 10;
		box-shadow: 0 24px 64px color-mix(in srgb, var(--text) 14%, transparent);
	}

	.ai-screencast video {
		display: block;
		width: 100%;
		height: 100%;
		object-fit: cover;
		object-position: center top;
	}

	.fade-overlay {
		position: absolute;
		inset: 0;
		z-index: 1;
		pointer-events: none;
		background: linear-gradient(
			to right,
			var(--background) 0%,
			var(--background) 15%,
			color-mix(in srgb, var(--background) 75%, transparent) 32%,
			color-mix(in srgb, var(--background) 30%, transparent) 48%,
			transparent 62%
		);
		transition: opacity 0.4s ease;
	}

	.ai-screencast:hover .fade-overlay {
		opacity: 0;
	}

	/* fade and lower the overlapping feature text while the video is hovered,
	   and let pointer events pass through so the hover holds across the frame */
	:global(.hds-feature-split:has(.ai-screencast:hover) .text-col) {
		opacity: 0.12;
		transform: translateY(12px);
		pointer-events: none;
		transition:
			opacity 0.4s ease,
			transform 0.4s ease;
	}

	@media (max-width: 900px) {
		.ai-screencast {
			display: none;
		}
	}
</style>

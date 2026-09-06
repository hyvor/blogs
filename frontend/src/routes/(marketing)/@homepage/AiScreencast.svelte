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
	<video bind:this={videoEl} muted loop playsinline preload="metadata">
		<source
			src="https://res.cloudinary.com/dqabfne6s/video/upload/v1788727146/blogs.hyvor.com/landing-videos/ai-screencast-1280w-30fps_mkcxfg.mp4"
			type="video/mp4"
		/>
		<track kind="captions" />
	</video>

	<div class="fade-overlay" aria-hidden="true"></div>
</div>

<style>
	.ai-screencast {
		--left-pull: 380px;
		--right-bleed: min(calc(max(0px, (100vw - 1000px) / 2) + 72px), 372px);
		position: relative;
		z-index: 1;
		margin-top: 64px;
		margin-left: calc(-1 * var(--left-pull));
		aspect-ratio: 2940 / 1586;
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
			--left-pull: 0px;
			--right-bleed: 0px;
			margin-top: 0;
			margin-left: -15px;
			width: calc(100% + 30px);
			border-radius: 0;
			border-left: none;
			border-right: none;
			box-shadow: 0 10px 30px color-mix(in srgb, var(--text) 8%, transparent);
		}

		.ai-screencast video {
			object-position: center top;
		}
		.fade-overlay {
			display: none;
		}
	}
</style>

<script lang="ts">
	import videoReviewPoster from '$lib/img/testimonials/video-review-poster.jpg';

	interface TextReview {
		type: 'text';
		name: string;
		role: string;
		quote: string;
	}

	interface VideoReview {
		type: 'video';
		name: string;
		role: string;
		// populate with a real clip once we have one recorded; the poster
		// photo above is a generic stand-in, not a real customer
		videoUrl?: string;
		posterUrl?: string;
	}

	type Review = TextReview | VideoReview;

	const reviews: Review[] = [
		{
			type: 'text',
			name: 'Lionel S.',
			role: 'Blogger',
			quote: 'I need a simple, easy-to-use, fast, beautiful and mature blogging tool that resolves the WordPress bloat. Hyvor Blogs handles this beautifully.'
		},
		{
			type: 'video',
			name: 'Video testimonial',
			role: 'Coming soon',
			posterUrl: videoReviewPoster
		},
		{
			type: 'text',
			name: 'Manoj P.',
			role: 'Senior Application Engineer',
			quote: 'The platform offers a seamless and user-friendly experience for both bloggers and readers. The customization options are extensive, allowing bloggers to create a unique and visually appealing blog.'
		}
	];

	// deterministic, non-photographic "identicon" per reviewer name — real people,
	// but nobody here has their actual photo, so we don't fake one
	function identicon(seed: string) {
		let hash = 0;
		for (let i = 0; i < seed.length; i++) {
			hash = (hash * 31 + seed.charCodeAt(i)) | 0;
		}
		const hue = Math.abs(hash) % 360;
		const cells: { x: number; y: number }[] = [];
		for (let col = 0; col < 3; col++) {
			for (let row = 0; row < 5; row++) {
				const bit = (hash >> (col * 5 + row)) & 1;
				if (!bit) continue;
				cells.push({ x: col, y: row });
				if (col < 2) cells.push({ x: 4 - col, y: row });
			}
		}
		return {
			bg: `hsl(${hue} 45% 92%)`,
			fg: `hsl(${hue} 50% 40%)`,
			cells
		};
	}

	let scrollEl: HTMLDivElement | undefined = $state();
	let dragging = $state(false);
	let dragStartX = 0;
	let dragStartScroll = 0;

	function onPointerDown(e: PointerEvent) {
		if (!scrollEl) return;
		dragging = true;
		dragStartX = e.clientX;
		dragStartScroll = scrollEl.scrollLeft;
		scrollEl.setPointerCapture(e.pointerId);
	}

	function onPointerMove(e: PointerEvent) {
		if (!dragging || !scrollEl) return;
		scrollEl.scrollLeft = dragStartScroll - (e.clientX - dragStartX);
	}

	function onPointerUp() {
		dragging = false;
	}
</script>

<section class="testimonials">
	<div class="hds-container head">
		<p class="label">Testimonials</p>
		<h2>Don't take our word for it.<br />Hear it from our customers.</h2>
	</div>

	<!-- svelte-ignore a11y_no_static_element_interactions -->
	<div
		class="scroll-row"
		class:dragging
		bind:this={scrollEl}
		onpointerdown={onPointerDown}
		onpointermove={onPointerMove}
		onpointerup={onPointerUp}
		onpointerleave={onPointerUp}
		role="region"
		aria-label="Customer testimonials"
	>
		{#each reviews as review}
			{#if review.type === 'text'}
				{@const av = identicon(review.name)}
				<figure class="card text-card hds-box">
					<svg
						class="avatar"
						viewBox="0 0 5 5"
						style="background: {av.bg}"
						aria-hidden="true"
					>
						{#each av.cells as cell}
							<rect x={cell.x} y={cell.y} width="1" height="1" fill={av.fg} />
						{/each}
					</svg>
					<blockquote>&ldquo;{review.quote}&rdquo;</blockquote>
					<figcaption>
						<span class="name">{review.name}</span>
						<span class="role">{review.role}</span>
					</figcaption>
				</figure>
			{:else}
				<figure class="card video-card hds-box">
					<div class="poster">
						<img src={review.posterUrl} alt="" />
						<div class="poster-scrim"></div>
					</div>

					<div class="video-avatar">
						<img src={review.posterUrl} alt="" />
					</div>

					<button class="play-btn" aria-label="Play video testimonial" disabled>
						<svg
							width="20"
							height="20"
							viewBox="0 0 16 16"
							fill="currentColor"
							aria-hidden="true"
						>
							<path d="M5 3.5v9l8-4.5-8-4.5z" />
						</svg>
					</button>

					<figcaption>
						<span class="name">{review.name}</span>
						<span class="role">{review.role}</span>
					</figcaption>
				</figure>
			{/if}
		{/each}
	</div>
</section>

<style>
	@import url(https://fonts.bunny.net/css?family=caveat:600,700);

	.testimonials {
		background: var(--accent-lightest);
		padding: 100px 0 96px;
		overflow: hidden;
	}

	.head {
		margin-bottom: 44px;
	}

	.label {
		font-size: 14px;
		font-weight: 600;
		letter-spacing: 0.09em;
		text-transform: uppercase;
		color: var(--accent);
		margin: 0 0 16px;
	}

	h2 {
		font-size: clamp(30px, 4vw, 44px);
		font-weight: 800;
		letter-spacing: -0.02em;
		line-height: 1.15;
		color: var(--text);
		margin: 0;
		font-family: var(--font-serif);
	}

	.scroll-row {
		display: flex;
		align-items: stretch;
		gap: 20px;
		overflow-x: auto;
		/* explicit, not left to default — a lone overflow-x: auto silently
		   forces overflow-y to auto too (per spec, when only one axis is
		   'visible'), which was clipping each card's box-shadow into a hard
		   line at the top/bottom of the row instead of letting it fade out */
		overflow-y: visible;
		scroll-snap-type: x proximity;
		cursor: grab;
		/* top/bottom padding must clear --box-shadow's 30px blur radius, or
		   the shadow still gets clipped by the row's own padding-box edge */
		padding: 32px 15px 36px max(15px, calc((100vw - 1000px) / 2));
		scrollbar-width: none;
		-webkit-overflow-scrolling: touch;
		touch-action: pan-y;
	}

	.scroll-row.dragging {
		cursor: grabbing;
		scroll-snap-type: none;
	}

	.scroll-row::-webkit-scrollbar {
		display: none;
	}

	.card {
		position: relative;
		flex: 0 0 auto;
		width: 320px;
		height: 440px;
		margin: 0;
		box-sizing: border-box;
		scroll-snap-align: start;
	}

	.text-card {
		padding: 28px;
		display: flex;
		flex-direction: column;
	}

	.avatar {
		width: 46px;
		height: 46px;
		border-radius: 50%;
		flex-shrink: 0;
		display: block;
		shape-rendering: crispEdges;
	}

	blockquote {
		font-size: 16px;
		line-height: 1.7;
		color: var(--text);
		margin: 20px 0 0;
		flex: 1;
	}

	.text-card figcaption {
		display: flex;
		flex-direction: column;
		gap: 0;
	}

	.name {
		font-family: 'Caveat', cursive;
		font-size: 28px;
		font-weight: 600;
		color: var(--text);
		line-height: 1.2;
	}

	.role {
		font-size: 13px;
		color: var(--text-light);
	}

	.video-card {
		width: 280px;
		overflow: hidden;
		padding: 0;
	}

	.poster {
		position: absolute;
		inset: 0;
	}

	.poster img {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
	}

	/* even out contrast across whatever poster photo lands here, so the
	   play button and captions stay legible */
	.poster-scrim {
		position: absolute;
		inset: 0;
		background: linear-gradient(160deg, rgba(20, 14, 12, 0.45), rgba(20, 14, 12, 0.65));
	}

	.video-avatar {
		position: absolute;
		top: 16px;
		left: 16px;
		width: 40px;
		height: 40px;
		border-radius: 50%;
		overflow: hidden;
		box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.85);
	}

	.video-avatar img {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
	}

	.play-btn {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		width: 56px;
		height: 56px;
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.18);
		backdrop-filter: blur(6px);
		-webkit-backdrop-filter: blur(6px);
		border: 1px solid rgba(255, 255, 255, 0.4);
		color: #fff;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: default;
		transition:
			transform 0.2s,
			background 0.2s;
	}

	.play-btn:not(:disabled):hover {
		transform: translate(-50%, -50%) scale(1.08);
		background: rgba(255, 255, 255, 0.28);
	}

	.play-btn svg {
		margin-left: 3px;
	}

	.video-card figcaption {
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		padding: 40px 20px 20px;
		background: linear-gradient(to top, rgba(0, 0, 0, 0.75), transparent);
		display: flex;
		flex-direction: column;
	}

	.video-card .name {
		color: #fff;
	}

	.video-card .role {
		color: rgba(255, 255, 255, 0.75);
	}

	@media (max-width: 600px) {
		/* keep in step with the wider .hds-container gutter set globally in
		   +layout.svelte at this same breakpoint */
		.scroll-row {
			padding: 32px 20px 36px max(20px, calc((100vw - 1000px) / 2));
		}
	}

	@media (max-width: 768px) {
		.head {
			text-align: center;
			margin-bottom: 32px;
		}

		.card {
			width: 280px;
			height: 400px;
		}

		.video-card {
			width: 240px;
		}
	}
</style>

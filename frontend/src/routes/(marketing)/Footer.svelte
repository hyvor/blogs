<script lang="ts">
	import IconEnvelope from '@hyvor/icons/IconEnvelope';
	import IconShieldCheck from '@hyvor/icons/IconShieldCheck';
	import IconGithub from '@hyvor/icons/IconGithub';
	import IconTwitterX from '@hyvor/icons/IconTwitterX';
	import IconLinkedin from '@hyvor/icons/IconLinkedin';
	import IconYoutube from '@hyvor/icons/IconYoutube';
	import IconBluesky from '@hyvor/icons/IconBluesky';
	import IconDiscord from '@hyvor/icons/IconDiscord';
	import IconLockFill from '@hyvor/icons/IconLockFill';

	const year = new Date().getFullYear();
	const BRAND_COLOR = '#896c6b';

	// a single 5-pointed star (outer/inner vertices alternating), centered on
	// its own origin — reused via `transform="translate(...)"` per position
	// below, rather than a plain dot, for the EU ring on the GDPR badge
	function starPath(outerR: number, innerR: number) {
		const points: string[] = [];
		for (let i = 0; i < 10; i++) {
			const angle = -Math.PI / 2 + (i * Math.PI) / 5;
			const r = i % 2 === 0 ? outerR : innerR;
			points.push(`${(r * Math.cos(angle)).toFixed(2)},${(r * Math.sin(angle)).toFixed(2)}`);
		}
		return `M${points.join('L')}Z`;
	}
	const gdprStarPath = starPath(1.7, 0.68);

	// positions for the 12-star EU ring on the small GDPR badge (see .gdpr-chip)
	const gdprStars = Array.from({ length: 12 }, (_, i) => {
		const angle = (i * 30 * Math.PI) / 180;
		return {
			x: 16 + 12 * Math.cos(angle),
			y: 16 + 12 * Math.sin(angle)
		};
	});

	let mascotInView = $state(false);

	function onView(node: HTMLElement, callback: () => void) {
		const observer = new IntersectionObserver(
			(entries) => {
				if (entries[0]?.isIntersecting) {
					setTimeout(() => {
						callback();
					}, 400);
					observer.disconnect();
				}
			},
			{ threshold: 0.3 }
		);
		observer.observe(node);
		return {
			destroy() {
				observer.disconnect();
			}
		};
	}

	const socials = [
		{ icon: IconTwitterX, href: 'https://x.com/HyvorHQ', label: 'X (Twitter)' },
		{ icon: IconGithub, href: 'https://github.com/hyvor/blogs', label: 'GitHub' },
		{ icon: IconDiscord, href: 'https://discord.com/invite/2WRJxQB', label: 'Discord' },
		{
			icon: IconLinkedin,
			href: 'https://www.linkedin.com/company/30240435',
			label: 'LinkedIn'
		},
		{ icon: IconYoutube, href: 'https://www.youtube.com/@HYVOR', label: 'YouTube' },
		{ icon: IconBluesky, href: 'https://bsky.app/profile/hyvor.com', label: 'Bluesky' }
	];

	const columns = [
		{
			title: 'Product',
			links: [
				{ href: '/console', label: 'Console' },
				{ href: '/themes', label: 'Themes' },
				{ href: '/pricing', label: 'Pricing' },
				{ href: '/docs', label: 'Docs' },
				{ href: '/hosting', label: 'Hosting' }
			]
		},
		{
			title: 'Legal',
			links: [
				{ href: '/terms', label: 'Terms of Service' },
				{ href: '/privacy', label: 'Privacy Policy' },
				{ href: 'https://hyvor.com/compliance', label: 'Compliance', external: true }
			]
		},
		{
			title: 'HYVOR',
			links: [
				{ href: 'https://hyvor.com', label: 'hyvor.com', external: true },
				{ href: 'https://hyvor.com/#letter', label: 'About', external: true },
				{ href: 'https://hyvor.com/security', label: 'Security', external: true },
				{ href: 'https://status.hyvor.com', label: 'System Status', external: true }
			]
		},
		{
			title: 'Alternatives',
			links: [
				{
					href: 'https://hyvor.com/compare/blogs/wordpress',
					label: 'WordPress Alternative',
					external: true
				},
				{
					href: 'https://hyvor.com/compare/blogs/ghost',
					label: 'Ghost Alternative',
					external: true
				},
				{
					href: 'https://hyvor.com/compare/blogs/medium',
					label: 'Medium Alternative',
					external: true
				},
				{
					href: 'https://hyvor.com/compare/blogs/blogger',
					label: 'Blogger Alternative',
					external: true
				}
			]
		}
	];
</script>

<div class="footer-outer">
	<div class="mascot-wrap" use:onView={() => (mascotInView = true)}>
		<img
			src="/logo.svg"
			alt="Hyvor Blogs"
			width="100"
			height="100"
			class:in-view={mascotInView}
		/>
	</div>

	<footer class="site-footer">
		<div class="hds-container-max footer-inner">
			<div class="top-row">
				<div class="brand">
					<span>Hyvor Blogs</span>
				</div>

				<div class="top-row-right">
					<a class="email" href="mailto:blogs.support@hyvor.com">
						<IconEnvelope size={14} />
						blogs.support@hyvor.com
					</a>

					<div class="socials">
						{#each socials as s}
							<a href={s.href} target="_blank" rel="nofollow" aria-label={s.label}>
								<s.icon size={16} />
							</a>
						{/each}
					</div>
				</div>
			</div>

			<div class="columns">
				{#each columns as col}
					<div class="col">
						<div class="col-title">{col.title}</div>
						{#each col.links as link}
							<a href={link.href} target={link.external ? '_blank' : undefined}
								>{link.label}</a
							>
						{/each}
					</div>
				{/each}
			</div>

			<div class="bottom-bar">
				<div>HYVOR &copy; {year}</div>
				<div class="bottom-center">
					<a class="gdpr-chip" href="https://hyvor.com/compliance" target="_blank">
						<span class="gdpr-chip-icon">
							<svg class="ring" viewBox="0 0 32 32" aria-hidden="true">
								<circle cx="16" cy="16" r="16" fill="#173a8a" />
								{#each gdprStars as s}
									<path
										d={gdprStarPath}
										fill="#ffcd3c"
										transform="translate({s.x}, {s.y})"
									/>
								{/each}
							</svg>
							<span class="lock"><IconLockFill size={10} /></span>
						</span>
						<span class="gdpr-chip-text">
							<span class="l1">GDPR Compliant</span>
						</span>
					</a>
				</div>
				<div class="bottom-right">
					<div class="france">
						From France <span class="flag">🇫🇷</span>
					</div>
				</div>
			</div>
		</div>
	</footer>
</div>

<style>
	.footer-outer {
		position: relative;
	}

	.mascot-wrap {
		position: absolute;
		z-index: 10;
		display: flex;
		justify-content: center;
		pointer-events: none;
		left: 0;
		bottom: 100%;
		transform: translate(-35%, 45%) rotate(20deg);
	}

	.mascot-wrap img {
		opacity: 0;
	}

	.mascot-wrap img.in-view {
		animation: mascot-tilt-in 0.8s ease forwards;
	}

	@keyframes mascot-tilt-in {
		0% {
			opacity: 0;
			transform: rotate(-14deg) scale(0.9) translateY(16px);
		}
		55% {
			opacity: 1;
			transform: rotate(10deg) scale(1.06) translateY(-4px);
		}
		100% {
			opacity: 1;
			transform: rotate(0deg) scale(1) translateY(0);
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.mascot-wrap img {
			opacity: 1;
		}

		.mascot-wrap img.in-view {
			animation: none;
		}
	}

	.site-footer {
		position: relative;
		z-index: 1;
		background: #574443;
		padding-top: 50px;
		color: rgba(255, 255, 255, 0.75);
	}

	.top-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 20px;
		padding-bottom: 40px;
		border-bottom: 1px solid rgba(255, 255, 255, 0.08);
	}

	.brand {
		display: flex;
		align-items: center;
		gap: 10px;
		font-size: 16px;
		font-weight: 700;
		color: #fff;
	}

	.top-row-right {
		display: flex;
		align-items: center;
		gap: 24px;
		flex-wrap: wrap;
	}

	.email {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		font-size: 13px;
		color: rgba(255, 255, 255, 0.75);
	}

	.email:hover {
		color: #fff;
	}

	.socials {
		display: flex;
		align-items: center;
		gap: 14px;
	}

	.socials a {
		color: rgba(255, 255, 255, 0.65);
		display: flex;
	}

	.socials a:hover {
		color: #fff;
	}

	.columns {
		display: flex;
		flex-wrap: wrap;
		gap: 40px;
		padding: 48px 0;
	}

	.col {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		min-width: 150px;
		flex: 1;
	}

	.col-title {
		font-size: 12px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		color: rgba(255, 255, 255, 0.45);
		margin-bottom: 14px;
	}

	.col a {
		font-size: 14px;
		color: rgba(255, 255, 255, 0.75);
		margin-top: 10px;
	}

	.col a:first-of-type {
		margin-top: 0;
	}

	.col a:hover {
		color: #fff;
		text-decoration: underline;
	}

	.bottom-bar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 10px;
		padding: 24px 0 32px;
		border-top: 1px solid rgba(255, 255, 255, 0.08);
		font-size: 13px;
		color: rgba(255, 255, 255, 0.5);
	}

	.bottom-right {
		display: flex;
		align-items: center;
		gap: 16px;
		flex-wrap: wrap;
	}

	.france {
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}

	.gdpr-chip {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 5px 12px 5px 5px;
		border-radius: 100px;
		background: color-mix(in srgb, #6779a3 20%, transparent 80%);
		color: #fff;
		opacity: 0.9;
		transition: opacity 0.15s ease;
	}

	.gdpr-chip:hover {
		opacity: 1;
	}

	.gdpr-chip-icon {
		position: relative;
		width: 22px;
		height: 22px;
		flex-shrink: 0;
	}

	.gdpr-chip-icon .ring {
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
		display: block;
	}

	.gdpr-chip-icon .lock {
		position: absolute;
		inset: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #fff;
	}

	.gdpr-chip-text {
		display: flex;
		flex-direction: column;
		line-height: 1.2;
	}

	.gdpr-chip-text .l1 {
		font-size: 11px;
		font-weight: 800;
		letter-spacing: 0.02em;
		color: #fff;
	}

	.gdpr-chip-text .l2 {
		font-size: 8px;
		font-weight: 600;
		letter-spacing: 0.06em;
		text-transform: uppercase;
		color: rgba(255, 255, 255, 0.85);
	}

	/* emoji glyphs are pre-colored — never let the surrounding muted text
	   color/opacity dim or filter them */
	.flag {
		color: initial;
		opacity: 1;
		filter: none;
	}

	@media (max-width: 900px) {
		.columns {
			gap: 32px;
		}

		.col {
			min-width: 45%;
		}
	}

	@media (max-width: 560px) {
		.mascot-wrap {
			/* keep it clear of the "Hyvor Blogs" wordmark that sits right below it */
			margin-bottom: -40px;
		}

		.site-footer {
			padding-top: 130px;
		}

		.top-row {
			flex-direction: column;
			align-items: flex-start;
		}

		.col {
			min-width: 100%;
		}
	}
</style>

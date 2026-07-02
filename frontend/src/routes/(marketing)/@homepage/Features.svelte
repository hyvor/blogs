<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';


	const hostingOptions = [
		{ domain: 'yourblog.hyvorblogs.io', label: 'Default subdomain', note: 'Zero setup' },
		{ domain: 'blog.yoursite.com', label: 'Custom domain', note: 'Free SSL certificate' },
		{ domain: 'yoursite.com/blog', label: 'Sub-directory', note: 'Best for SEO' },
	];

	const seoItems = [
		'Meta tags, Open Graph, Twitter Cards',
		'Auto-generated sitemaps & robots.txt',
		'In-built SEO & link analyzers',
		'Static HTML with zero JS bloat',
		'Automatic WebP image conversion',
	];

	const languages = [
		{ code: 'EN', name: 'English',  dir: 'ltr' },
		{ code: 'FR', name: 'Français', dir: 'ltr' },
		{ code: 'AR', name: 'العربية', dir: 'rtl' },
		{ code: 'DE', name: 'Deutsch',  dir: 'ltr' },
	];
</script>

<!-- ── Section 1: Custom Themes (text left, visual right) ─────────────── -->
<section class="feature-section">
	<div class="hds-container split">
		<div class="text-col">
			<span class="eyebrow">Custom Themes</span>
			<h2>Your blog, your brand</h2>
			<p>
				Choose from a library of beautiful themes or build your own with plain HTML and CSS. Every
				colour, font, and layout detail is yours to control.
			</p>
			<ul class="bullets">
				<li><IconCheckCircleFill size={14} /> Original &amp; ported themes included</li>
				<li><IconCheckCircleFill size={14} /> Open-source, fork and customise freely</li>
				<li><IconCheckCircleFill size={14} /> Theme development docs &amp; API</li>
			</ul>
			<Button as="a" href="/themes" variant="outline" size="small">
				Browse themes
				{#snippet end()}<IconBoxArrowUpRight size={11} />{/snippet}
			</Button>
		</div>

		<div class="visual-col themes-col">
			<!-- Back mockup: dark theme -->
			<div class="theme-mockup theme-mockup-back">
				<div class="tmock-bar dark">
					<span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
					<span class="tmock-url">yourblog.com</span>
				</div>
				<div class="tmock-body dark">
					<div class="tb-header">
						<div class="tb-logo"></div>
						<div class="tb-nav"><span></span><span></span><span></span></div>
					</div>
					<div class="tb-hero"></div>
					<div class="tb-content">
						<div class="tb-tag"></div>
						<div class="tb-title wide"></div>
						<div class="tb-title narrow"></div>
						<div class="tb-meta"></div>
						<div class="tb-text"></div>
						<div class="tb-text"></div>
						<div class="tb-text short"></div>
					</div>
				</div>
			</div>

			<!-- Front mockup: light theme (blog listing, amber/orange) -->
			<div class="theme-mockup theme-mockup-front">
				<div class="tmock-bar">
					<span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
					<span class="tmock-url">yourblog.com</span>
				</div>
				<div class="tmock-body light">
					<div class="tbl-header">
						<div class="tb-logo light"></div>
						<div class="tbl-subscribe"></div>
					</div>
					<div class="tbl-featured">
						<div class="tbl-featured-img"></div>
						<div class="tbl-featured-content">
							<div class="tbl-tag"></div>
							<div class="tbl-ftitle wide"></div>
							<div class="tbl-ftitle narrow"></div>
							<div class="tbl-fmeta"></div>
						</div>
					</div>
					<div class="tbl-cards">
						<div class="tbl-card">
							<div class="tbl-card-img"></div>
							<div class="tbl-card-line wide"></div>
							<div class="tbl-card-line narrow"></div>
						</div>
						<div class="tbl-card">
							<div class="tbl-card-img"></div>
							<div class="tbl-card-line wide"></div>
							<div class="tbl-card-line narrow"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ── Section 2: SEO & Speed (visual left, text right) ──────────────── -->
<section class="feature-section alt-bg">
	<div class="hds-container split flip">
		<div class="visual-col">
			<div class="seo-mockup">
				<div class="scores-wrap">
					{#each ['Performance', 'Accessibility', 'Best Practices', 'SEO'] as name}
						<div class="score-item">
							<div class="score-ring">100</div>
							<span>{name}</span>
						</div>
					{/each}
				</div>
				<div class="analyzer-cards">
					<div class="analyzer-card">
						<div class="ac-header">
							<div class="ac-dot" style="background:#22c55e"></div>
							<span class="ac-title">SEO Analyzer</span>
						</div>
						<div class="ac-rows">
							{#each ['Title tag', 'Meta description', 'Open Graph', 'Canonical URL'] as item}
								<div class="ac-row"><span class="ac-check">✓</span>{item}</div>
							{/each}
						</div>
						<div class="ac-footer">Real-time feedback</div>
					</div>

					<div class="analyzer-card">
						<div class="ac-header">
							<div class="ac-dot" style="background:#3b82f6"></div>
							<span class="ac-title">Post Link Analyzer</span>
						</div>
						<div class="ac-rows">
							{#each [['Internal links', '12'], ['External links', '4'], ['Broken links', '0'], ['Redirects', '1']] as [label, val]}
								<div class="ac-row ac-row-kv"><span>{label}</span><span class="ac-val">{val}</span></div>
							{/each}
						</div>
						<div class="ac-footer">Per-post analysis</div>
					</div>

					<div class="analyzer-card">
						<div class="ac-header">
							<div class="ac-dot" style="background:#f59e0b"></div>
							<span class="ac-title">Full-blog Link Analyzer</span>
						</div>
						<div class="ac-rows">
							{#each [['Links checked', '248'], ['Issues found', '0'], ['Redirects', '3'], ['Last scan', '2d ago']] as [label, val]}
								<div class="ac-row ac-row-kv"><span>{label}</span><span class="ac-val">{val}</span></div>
							{/each}
						</div>
						<div class="ac-footer">Runs every 2 weeks</div>
					</div>
				</div>
			</div>
		</div>

		<div class="text-col">
			<span class="eyebrow">SEO &amp; Speed</span>
			<h2>Perfect scores, zero configuration</h2>
			<p>
				Every blog is automatically optimised for search engines and page speed. You write; we handle
				the technical SEO so your content ranks.
			</p>
			<ul class="bullets">
				{#each seoItems as item}
					<li><IconCheckCircleFill size={14} />{item}</li>
				{/each}
			</ul>
		</div>
	</div>
</section>

<!-- ── Section 3: Hosting (text left, visual right) ──────────────────── -->
<section class="feature-section">
	<div class="hds-container split">
		<div class="text-col">
			<span class="eyebrow">Flexible Hosting</span>
			<h2>Host it anywhere you like</h2>
			<p>
				Start on a subdomain in seconds, move to a custom domain, or serve your blog from inside
				your existing site with a sub-directory. Your call.
			</p>
			<ul class="bullets">
				<li><IconCheckCircleFill size={14} /> Free SSL on all custom domains</li>
				<li><IconCheckCircleFill size={14} /> Cloudflare Workers, Docker, Next.js, Laravel&hellip;</li>
				<li><IconCheckCircleFill size={14} /> Reverse proxy support for sub-directory</li>
			</ul>
		</div>

		<div class="visual-col">
			<div class="hosting-mockup">
				{#each hostingOptions as opt, i}
					<div class="hosting-card" class:active={i === 1}>
						<div class="hc-top">
							{#if i === 1}<span class="hc-badge">Most popular</span>{/if}
						</div>
						<div class="hc-domain">{opt.domain}</div>
						<div class="hc-label">{opt.label}</div>
						<div class="hc-note">{opt.note}</div>
					</div>
				{/each}
			</div>
		</div>
	</div>
</section>

<!-- ── Section 4: Multi-language (visual left, text right) ───────────── -->
<section class="feature-section alt-bg">
	<div class="hds-container split flip">
		<div class="visual-col">
			<div class="ml-mockup">
				<div class="ml-header">
					<div class="ml-post-title"></div>
					<div class="ml-lang-switcher">
						{#each languages as lang}
							<div class="lang-chip" class:active={lang.code === 'FR'}>
								<span class="lang-code">{lang.code}</span>
							</div>
						{/each}
					</div>
				</div>
				<div class="ml-body">
					{#each languages as lang}
						<div class="lang-row" class:rtl={lang.dir === 'rtl'}>
							<div class="lang-flag-code">{lang.code}</div>
							<div class="lang-lines">
								<div class="lang-line long"></div>
								<div class="lang-line med"></div>
							</div>
							<div class="lang-status" class:done={lang.code !== 'DE'}>{lang.code === 'DE' ? '·' : '✓'}</div>
						</div>
					{/each}
				</div>
				<div class="ml-footer">
					<div class="rtl-badge">RTL supported</div>
					<div class="ml-note">AI translation available</div>
				</div>
			</div>
		</div>

		<div class="text-col">
			<span class="eyebrow">Multi-language</span>
			<h2>Reach a global audience</h2>
			<p>
				Translate posts, tags, author names and everything else. Add languages in one click and Hyvor Blogs
				handles routing, hreflang tags, and RTL layouts automatically.
			</p>
			<ul class="bullets">
				<li><IconCheckCircleFill size={14} /> RTL language support built-in</li>
				<li><IconCheckCircleFill size={14} /> Integrated AI translator</li>
				<li><IconCheckCircleFill size={14} /> Automatic hreflang &amp; i18n routing</li>
			</ul>
		</div>
	</div>
</section>

<style lang="scss">
	/* ── Layout ──────────────────────────────────────────────── */
	.feature-section {
		padding: 100px 0;
		border-bottom: 1px solid var(--border);
	}

	.alt-bg {
		background: color-mix(in srgb, var(--accent) 3%, var(--background));
	}

	.split {
		display: flex;
		align-items: center;
		gap: 72px;

		&.flip {
			flex-direction: row-reverse;
		}
	}

	.text-col,
	.visual-col {
		flex: 1;
		min-width: 0;
	}

	/* ── Text column ─────────────────────────────────────────── */
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
		font-size: clamp(26px, 3.5vw, 38px);
		font-weight: 800;
		letter-spacing: -0.02em;
		line-height: 1.15;
		margin: 0 0 16px;
	}

	p {
		font-size: 16px;
		line-height: 1.7;
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

		li {
			display: flex;
			align-items: center;
			gap: 10px;
			font-size: 1rem;

			:global(svg) {
				color: var(--accent);
				flex-shrink: 0;
			}
		}
	}

	/* ── Shared mockup shell ─────────────────────────────────── */
	.mockup-browser {
		border-radius: 20px;
		overflow: hidden;
		border: 1px solid var(--border);
		box-shadow:
			0 0 0 1px var(--border),
			0 24px 64px color-mix(in srgb, var(--text) 10%, transparent);
	}

	.mock-bar {
		display: flex;
		align-items: center;
		gap: 6px;
		padding: 10px 14px;
		background: #f1f1f3;
		border-bottom: 1px solid var(--border);
	}

	:global(:root.dark) .mock-bar {
		background: #1e1e22;
	}

	.dot {
		display: block;
		width: 11px;
		height: 11px;
		border-radius: 50%;
		&.r { background: #ff5f57; }
		&.y { background: #febc2e; }
		&.g { background: #28c840; }
	}

	.mock-url {
		flex: 1;
		text-align: center;
		font-size: 11px;
		color: var(--text-light);
		background: var(--background);
		border: 1px solid var(--border);
		border-radius: 4px;
		padding: 3px 10px;
		max-width: 180px;
		margin: 0 auto;
	}

	/* ── Themes visual: overlapping mockups ─────────────────── */
	.themes-col {
		position: relative;
		height: 400px;
	}

	.theme-mockup {
		position: absolute;
		width: 78%;
		border-radius: 20px;
		overflow: hidden;
		border: 1px solid var(--border);
	}

	.theme-mockup-back {
		top: 0;
		right: 0;
		transform: rotate(3.5deg);
		transform-origin: top right;
		z-index: 1;
		box-shadow: 0 16px 48px rgba(0, 0, 0, 0.3);
	}

	.theme-mockup-front {
		bottom: 0;
		left: 0;
		transform: rotate(-2deg);
		transform-origin: bottom left;
		z-index: 2;
		box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
	}

	.tmock-bar {
		display: flex;
		align-items: center;
		gap: 6px;
		padding: 9px 12px;
		background: #f1f1f3;
		border-bottom: 1px solid #e5e7eb;

		&.dark {
			background: #1a1a2e;
			border-bottom-color: rgba(255, 255, 255, 0.06);
		}
	}

	.tmock-url {
		flex: 1;
		text-align: center;
		font-size: 10px;
		padding: 2px 8px;
		border-radius: 4px;
		max-width: 130px;
		margin: 0 auto;
		background: white;
		color: #6b7280;
		border: 1px solid #e5e7eb;

		.dark & {
			background: rgba(255, 255, 255, 0.06);
			color: rgba(255, 255, 255, 0.3);
			border-color: rgba(255, 255, 255, 0.08);
		}
	}

	.tmock-body {
		&.light { background: #ffffff; }
		&.dark  { background: #0d0d1a; }
	}

	.tb-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 11px 14px;

		.light & { border-bottom: 1px solid #f3f4f6; }
		.dark &  { border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
	}

	.tb-logo {
		width: 56px;
		height: 9px;
		border-radius: 3px;
		.light & { background: #1f2937; }
		.dark &  { background: rgba(255, 255, 255, 0.65); }
		&.light  { background: #5c4233; }
	}

	.tb-nav {
		display: flex;
		gap: 8px;
		span {
			display: block;
			width: 28px;
			height: 7px;
			border-radius: 3px;
		}
		.light & span { background: #d1d5db; }
		.dark &  span { background: rgba(255, 255, 255, 0.12); }
	}

	.tb-hero {
		height: 90px;
		.light & { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
		.dark &  { background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 100%); }
	}

	.tb-content {
		padding: 13px 14px 16px;
	}

	.tb-tag {
		width: 48px;
		height: 14px;
		border-radius: 100px;
		margin-bottom: 9px;
		.light & { background: #e0e7ff; }
		.dark &  { background: rgba(99, 102, 241, 0.3); }
	}

	.tb-title {
		height: 11px;
		border-radius: 3px;
		margin-bottom: 6px;
		&.wide   { width: 90%; }
		&.narrow { width: 68%; }
		.light & { background: #1f2937; }
		.dark &  { background: rgba(255, 255, 255, 0.7); }
	}

	.tb-meta {
		width: 90px;
		height: 7px;
		border-radius: 3px;
		margin-bottom: 11px;
		.light & { background: #d1d5db; }
		.dark &  { background: rgba(255, 255, 255, 0.18); }
	}

	.tb-text {
		height: 7px;
		border-radius: 3px;
		margin-bottom: 7px;
		width: 100%;
		&.short { width: 62%; }
		.light & { background: #e5e7eb; }
		.dark &  { background: rgba(255, 255, 255, 0.09); }
	}

	/* ── Light theme: blog listing layout (warm sand) ────────── */
	.tbl-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 10px 14px;
		border-bottom: 1px solid #e8ddd0;
		background: #f7f3ee;
	}

	.tbl-subscribe {
		width: 52px;
		height: 20px;
		border-radius: 100px;
		background: #b08968;
	}

	.tbl-featured {
		display: flex;
		gap: 10px;
		padding: 12px 14px 10px;
		border-bottom: 1px solid #f0ebe4;
	}

	.tbl-featured-img {
		width: 72px;
		height: 60px;
		border-radius: 8px;
		flex-shrink: 0;
		background: linear-gradient(135deg, #b08968, #8a6a8a);
	}

	.tbl-featured-content {
		flex: 1;
		min-width: 0;
		display: flex;
		flex-direction: column;
		gap: 5px;
		padding-top: 2px;
	}

	.tbl-tag {
		width: 40px;
		height: 12px;
		border-radius: 100px;
		background: #e2d0bf;
	}

	.tbl-ftitle {
		height: 10px;
		border-radius: 3px;
		background: #aaaaaa;
		&.wide   { width: 95%; }
		&.narrow { width: 75%; }
	}

	.tbl-fmeta {
		width: 80px;
		height: 7px;
		border-radius: 3px;
		background: #c9bdb2;
		margin-top: 2px;
	}

	.tbl-cards {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 8px;
		padding: 10px 14px 12px;
		background: #f7f3ee;
	}

	.tbl-card {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}

	.tbl-card-img {
		height: 40px;
		border-radius: 6px;
		background: linear-gradient(135deg, #c9a882, #a08878);
		opacity: 0.9;
	}

	.tbl-card-line {
		height: 7px;
		border-radius: 3px;
		background: #aaaaaa;
		&.wide   { width: 90%; }
		&.narrow { width: 65%; }
	}

	/* ── SEO visual ──────────────────────────────────────────── */
	.seo-mockup {
		display: flex;
		flex-direction: column;
		gap: 16px;
	}

	.scores-wrap {
		display: flex;
		justify-content: space-between;
		padding: 24px;
		border-radius: 20px;
		border: 1px solid var(--border);
		background: var(--background);
		box-shadow: 0 8px 32px color-mix(in srgb, var(--text) 6%, transparent);
	}

	.score-item {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 8px;

		span {
			font-size: 11px;
			font-weight: 500;
			color: var(--text-light);
			text-align: center;
			max-width: 58px;
			line-height: 1.3;
		}
	}

	.score-ring {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 56px;
		height: 56px;
		border-radius: 50%;
		border: 5px solid #22c55e;
		background: color-mix(in srgb, #22c55e 10%, transparent);
		color: #16a34a;
		font-size: 16px;
		font-weight: 700;
	}

	.analyzer-cards {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 10px;
	}

	.analyzer-card {
		border-radius: 20px;
		border: 1px solid var(--border);
		background: var(--background);
		overflow: hidden;
		box-shadow: 0 4px 16px color-mix(in srgb, var(--text) 5%, transparent);
		display: flex;
		flex-direction: column;
	}

	.ac-header {
		display: flex;
		align-items: center;
		gap: 7px;
		padding: 11px 14px;
		border-bottom: 1px solid var(--border);
	}

	.ac-dot {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		flex-shrink: 0;
	}

	.ac-title {
		font-size: 12px;
		font-weight: 600;
		color: var(--text);
		line-height: 1.3;
	}

	.ac-rows {
		padding: 8px 0;
		flex: 1;
	}

	.ac-row {
		display: flex;
		align-items: center;
		gap: 7px;
		padding: 5px 14px;
		font-size: 12px;
		color: var(--text);
	}

	.ac-check {
		font-size: 11px;
		color: #22c55e;
		font-weight: 700;
		flex-shrink: 0;
	}

	.ac-row-kv {
		justify-content: space-between;
		gap: 4px;

		span:first-child {
			color: var(--text-light);
		}
	}

	.ac-val {
		font-weight: 600;
		color: var(--text);
	}

	.ac-footer {
		font-size: 11px;
		color: var(--text-light);
		padding: 8px 14px;
		border-top: 1px solid var(--border);
		background: color-mix(in srgb, var(--text) 2%, var(--background));
	}

	/* ── Hosting visual ──────────────────────────────────────── */
	.hosting-mockup {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	.hosting-card {
		border-radius: 20px;
		border: 1px solid var(--border);
		padding: 16px 20px;
		background: var(--background);
		transition: box-shadow 0.2s, border-color 0.2s;
		box-shadow: 0 2px 8px color-mix(in srgb, var(--text) 4%, transparent);

		&.active {
			border-color: var(--accent);
			box-shadow: 0 0 0 1px var(--accent), 0 8px 24px color-mix(in srgb, var(--accent) 15%, transparent);
		}
	}

	.hc-top {
		min-height: 20px;
		margin-bottom: 4px;
	}

	.hc-badge {
		font-size: 11px;
		font-weight: 600;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 12%, transparent);
		padding: 2px 8px;
		border-radius: 100px;
	}

	.hc-domain {
		font-family: monospace;
		font-size: 14px;
		font-weight: 600;
		color: var(--accent);
		margin-bottom: 4px;
	}

	.hc-label {
		font-size: 13px;
		font-weight: 600;
		margin-bottom: 2px;
	}

	.hc-note {
		font-size: 12px;
		color: var(--text-light);
	}

	/* ── Multi-language visual ───────────────────────────────── */
	.ml-mockup {
		border-radius: 20px;
		border: 1px solid var(--border);
		overflow: hidden;
		background: var(--background);
		box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent);
	}

	.ml-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 16px 20px;
		border-bottom: 1px solid var(--border);
		gap: 16px;
	}

	.ml-post-title {
		flex: 1;
		height: 14px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 15%, transparent);
		max-width: 160px;
	}

	.ml-lang-switcher {
		display: flex;
		gap: 6px;
	}

	.lang-chip {
		padding: 4px 10px;
		border-radius: 6px;
		font-size: 12px;
		font-weight: 600;
		border: 1px solid var(--border);
		color: var(--text-light);
		background: var(--background);

		&.active {
			background: var(--accent);
			color: #fff;
			border-color: var(--accent);
		}
	}

	.ml-body {
		padding: 12px 20px;
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.lang-row {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 8px 0;
		border-bottom: 1px solid color-mix(in srgb, var(--border) 60%, transparent);

		&.rtl .lang-lines {
			direction: rtl;
		}
	}

	.lang-flag-code {
		font-size: 11px;
		font-weight: 700;
		color: var(--text-light);
		width: 26px;
		text-align: center;
	}

	.lang-lines {
		flex: 1;
		display: flex;
		flex-direction: column;
		gap: 5px;
	}

	.lang-line {
		height: 8px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 10%, transparent);
		&.long { width: 90%; }
		&.med { width: 65%; }
	}

	.lang-status {
		font-size: 13px;
		font-weight: 700;
		width: 20px;
		text-align: center;
		color: var(--text-light);
		&.done { color: #22c55e; }
	}

	.ml-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 14px 20px;
		border-top: 1px solid var(--border);
		background: color-mix(in srgb, var(--accent) 4%, var(--background));
	}

	.rtl-badge {
		font-size: 12px;
		font-weight: 600;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 10%, transparent);
		padding: 3px 10px;
		border-radius: 100px;
		border: 1px solid color-mix(in srgb, var(--accent) 25%, transparent);
	}

	.ml-note {
		font-size: 12px;
		color: var(--text-light);
	}

	/* ── Responsive ──────────────────────────────────────────── */
	@media (max-width: 900px) {
		.split,
		.split.flip {
			flex-direction: column;
			gap: 48px;
		}

		p {
			max-width: 100%;
		}

		.themes-col {
			height: 340px;
			width: 100%;
		}
	}
</style>

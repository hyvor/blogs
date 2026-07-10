<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import IconGlobe from '@hyvor/icons/IconGlobe';
	import IconPlusCircle from '@hyvor/icons/IconPlusCircle';
	import IconTranslate from '@hyvor/icons/IconTranslate';

	const themeColors = [
		{ primary: '#6366f1', surface: '#eef2ff' },
		{ primary: '#0ea5e9', surface: '#e0f2fe' },
		{ primary: '#10b981', surface: '#d1fae5' },
		{ primary: '#f59e0b', surface: '#fef3c7' },
		{ primary: '#ec4899', surface: '#fce7f3' },
		{ primary: '#8b5cf6', surface: '#ede9fe' }
	];

	const hostingTypes = [
		{
			domain: 'yourblog.hyvorblogs.io',
			label: 'Default subdomain',
			note: 'Zero setup, ready instantly'
		},
		{ domain: 'blog.yoursite.com', label: 'Custom domain', note: 'Free SSL, easy DNS setup' },
		{ domain: 'yoursite.com/blog', label: 'Sub-directory', note: 'Best for SEO, reverse proxy' }
	];

	const seoFeatures = [
		'Meta tags, Open Graph, Twitter Cards',
		'Auto-generated sitemaps & robots.txt',
		'In-built SEO & link analyzers',
		'Static HTML, no JS bloat',
		'Automatic WebP conversion'
	];

	const mlSteps = [
		{
			icon: IconPlusCircle,
			title: 'Add a language',
			desc: 'Name, code,done. Everything is ready for translation.'
		},
		{
			icon: IconTranslate,
			title: 'Translate content',
			desc: 'Posts, tags, author names,manually or with AI.'
		},
		{
			icon: IconGlobe,
			title: 'Reach global audiences',
			desc: 'Hyvor Blogs handles routing, hreflang, and RTL automatically.'
		}
	];
</script>

<section class="bento hds-container">
	<div class="grid">
		<!-- Custom Themes (large) -->
		<div class="card card-themes">
			<div class="card-body">
				<span class="badge" style="--badge-color: #6366f1">Themes</span>
				<h3>Match your brand, perfectly</h3>
				<p>
					Choose from a library of beautiful themes or build your own with HTML and CSS. Every
					detail is customizable.
				</p>
				<Button as="a" href="/themes" size="small" variant="outline">
					Browse themes
					{#snippet end()}
						<IconBoxArrowUpRight size={11} />
					{/snippet}
				</Button>
			</div>
			<div class="themes-visual">
				{#each themeColors as theme}
					<div class="theme-swatch">
						<div class="swatch-header" style="background:{theme.primary}"></div>
						<div class="swatch-body" style="background:{theme.surface}">
							<div class="swatch-line long" style="background:{theme.primary}20"></div>
							<div class="swatch-line short" style="background:{theme.primary}20"></div>
							<div class="swatch-line med" style="background:{theme.primary}20"></div>
						</div>
					</div>
				{/each}
			</div>
		</div>

		<!-- Hosting (small) -->
		<div class="card card-hosting">
			<span class="badge" style="--badge-color: #0ea5e9">Hosting</span>
			<h3>Host anywhere</h3>
			<p>Subdomain, custom domain, or sub-directory,your blog lives where you want it.</p>
			<div class="hosting-list">
				{#each hostingTypes as type}
					<div class="hosting-item">
						<div class="hosting-domain">{type.domain}</div>
						<div class="hosting-meta">
							<strong>{type.label}</strong>
							<span>{type.note}</span>
						</div>
					</div>
				{/each}
			</div>
		</div>

		<!-- Multi-language (small) -->
		<div class="card card-ml">
			<span class="badge" style="--badge-color: #10b981">Multi-language</span>
			<h3>Go global in minutes</h3>
			<p>
				Everything is translatable,posts, tags, authors. RTL languages supported out of the box.
			</p>
			<div class="ml-steps">
				{#each mlSteps as step, i}
					<div class="ml-step">
						<div class="step-num">{i + 1}</div>
						<div class="step-body">
							<div class="step-title">{step.title}</div>
							<div class="step-desc">{step.desc}</div>
						</div>
					</div>
				{/each}
			</div>
		</div>

		<!-- SEO & Speed (large) -->
		<div class="card card-seo">
			<div class="card-body">
				<span class="badge" style="--badge-color: #f59e0b">SEO &amp; Speed</span>
				<h3>Perfect scores. Zero configuration.</h3>
				<p>
					All blogs are automatically optimized. Static HTML, instant CDN delivery, and built-in SEO
					tools.
				</p>
				<ul class="seo-features">
					{#each seoFeatures as feat}
						<li>
							<IconCheckCircleFill size={14} />
							{feat}
						</li>
					{/each}
				</ul>
			</div>
			<div class="scores-visual">
				<div class="scores-label">Lighthouse scores</div>
				<div class="scores-row">
					{#each ['Performance', 'Accessibility', 'Best Practices', 'SEO'] as label}
						<div class="score-circle">
							<span class="score-num">100</span>
							<span class="score-label">{label}</span>
						</div>
					{/each}
				</div>
			</div>
		</div>
	</div>
</section>

<style lang="scss">
	.bento {
		padding: 0 0 80px;
	}

	.grid {
		display: grid;
		grid-template-columns: 7fr 5fr;
		grid-template-rows: auto auto;
		gap: 16px;
	}

	/* Card base */
	.card {
		border-radius: 16px;
		border: 1px solid var(--border);
		overflow: hidden;
		padding: 32px;
		background: var(--background);
	}

	h3 {
		font-size: 22px;
		font-weight: 700;
		margin: 12px 0 8px;
		line-height: 1.2;
	}

	p {
		font-size: 15px;
		color: var(--text-light);
		line-height: 1.6;
		margin: 0 0 20px;
	}

	.badge {
		display: inline-flex;
		align-items: center;
		padding: 3px 10px;
		border-radius: 100px;
		font-size: 12px;
		font-weight: 600;
		background: color-mix(in srgb, var(--badge-color) 12%, transparent);
		color: var(--badge-color);
		border: 1px solid color-mix(in srgb, var(--badge-color) 25%, transparent);
	}

	/* Themes card */
	.card-themes {
		grid-column: 1;
		grid-row: 1;
		display: flex;
		gap: 32px;
		align-items: flex-start;
	}

	.card-themes .card-body {
		flex: 1;
		min-width: 0;
	}

	.themes-visual {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 10px;
		flex-shrink: 0;
		width: 220px;
	}

	.theme-swatch {
		border-radius: 8px;
		overflow: hidden;
		border: 1px solid var(--border);
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
	}

	.swatch-header {
		height: 36px;
	}

	.swatch-body {
		padding: 8px 7px;
		display: flex;
		flex-direction: column;
		gap: 5px;
	}

	.swatch-line {
		height: 5px;
		border-radius: 3px;
		&.long {
			width: 100%;
		}
		&.med {
			width: 80%;
		}
		&.short {
			width: 60%;
		}
	}

	/* Hosting card */
	.card-hosting {
		grid-column: 2;
		grid-row: 1;
	}

	.hosting-list {
		display: flex;
		flex-direction: column;
		gap: 10px;
		margin-top: 4px;
	}

	.hosting-item {
		border: 1px solid var(--border);
		border-radius: 10px;
		padding: 12px 14px;
		background: var(--background-2, var(--box-background));
	}

	.hosting-domain {
		font-size: 13px;
		font-family: monospace;
		color: var(--accent);
		margin-bottom: 4px;
		font-weight: 600;
	}

	.hosting-meta {
		display: flex;
		flex-direction: column;
		gap: 1px;
		strong {
			font-size: 13px;
			font-weight: 600;
		}
		span {
			font-size: 12px;
			color: var(--text-light);
		}
	}

	/* Multi-language card */
	.card-ml {
		grid-column: 1;
		grid-row: 2;
	}

	.ml-steps {
		display: flex;
		flex-direction: column;
		gap: 16px;
		margin-top: 4px;
	}

	.ml-step {
		display: flex;
		gap: 14px;
		align-items: flex-start;
	}

	.step-num {
		width: 28px;
		height: 28px;
		border-radius: 50%;
		background: color-mix(in srgb, var(--accent) 12%, transparent);
		color: var(--accent);
		font-size: 13px;
		font-weight: 700;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.step-title {
		font-size: 14px;
		font-weight: 600;
		margin-bottom: 2px;
	}

	.step-desc {
		font-size: 13px;
		color: var(--text-light);
		line-height: 1.4;
	}

	/* SEO card */
	.card-seo {
		grid-column: 2;
		grid-row: 2;
		display: flex;
		flex-direction: column;
		gap: 24px;
	}

	.card-seo .card-body {
		flex: 1;
	}

	.seo-features {
		list-style: none;
		margin: 0;
		padding: 0;
		display: flex;
		flex-direction: column;
		gap: 7px;

		li {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 14px;
			:global(svg) {
				color: #10b981;
				flex-shrink: 0;
			}
		}
	}

	.scores-visual {
		border-top: 1px solid var(--border);
		padding-top: 20px;
	}

	.scores-label {
		font-size: 12px;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		color: var(--text-light);
		margin-bottom: 14px;
	}

	.scores-row {
		display: flex;
		gap: 12px;
		justify-content: space-between;
	}

	.score-circle {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 6px;
	}

	.score-num {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 52px;
		height: 52px;
		border-radius: 50%;
		border: 5px solid #22c55e;
		background: color-mix(in srgb, #22c55e 10%, transparent);
		color: #16a34a;
		font-size: 15px;
		font-weight: 700;
	}

	.score-label {
		font-size: 11px;
		font-weight: 500;
		color: var(--text-light);
		text-align: center;
		max-width: 55px;
		line-height: 1.3;
	}

	/* Responsive */
	@media (max-width: 1024px) {
		.card-themes {
			flex-direction: column;
		}
		.themes-visual {
			width: 100%;
			grid-template-columns: repeat(6, 1fr);
		}
	}

	@media (max-width: 768px) {
		.grid {
			grid-template-columns: 1fr;
		}
		.card-themes,
		.card-hosting,
		.card-ml,
		.card-seo {
			grid-column: 1;
			grid-row: auto;
		}
		.themes-visual {
			grid-template-columns: repeat(3, 1fr);
			width: 100%;
		}
	}
</style>

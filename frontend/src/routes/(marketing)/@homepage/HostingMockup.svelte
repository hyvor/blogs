<script lang="ts">
	const subDirMethods = [
		{
			name: 'Cloudflare Workers',
			logo: '/images/homepage/hosting/cf-workers.svg',
			href: 'https://hyvor.com/blog/cloudflare-workers-blog'
		},
		{
			name: 'Docker',
			logo: '/images/homepage/hosting/docker.svg',
			href: 'https://hyvor.com/blog/docker-subdirectory-blog'
		},
		{
			name: 'Next.js',
			logo: '/images/homepage/hosting/next.svg',
			href: 'https://hyvor.com/blog/nextjs-blog'
		},
		{
			name: 'Laravel',
			logo: '/images/homepage/hosting/laravel.svg',
			href: 'https://hyvor.com/blog/laravel-blog'
		},
		{
			name: 'Symfony',
			logo: '/images/homepage/hosting/symfony.svg',
			href: 'https://hyvor.com/blog/symfony-blog'
		}
	];

	const hostingOptions = [
		{
			domain: 'blog.yoursite.com',
			label: 'Custom domain',
			note: 'Free SSL certificate',
			active: true,
			tags: [{ name: 'CNAME', recommended: false }]
		},
		{
			domain: 'yoursite.com/blog',
			label: 'Sub-directory',
			note: 'Best for SEO',
			methods: subDirMethods
		},
		{
			domain: 'your-own-frontend.com',
			label: 'Headless',
			note: 'Bring your own frontend',
			tags: [
				{ name: 'Data API', recommended: false },
				{ name: 'Any framework', recommended: false }
			]
		}
	];
</script>

<div class="hosting-mockup">
	{#each hostingOptions as opt}
		<div class="hosting-card" class:active={opt.active}>
			<div class="hc-top">
				{#if opt.active}<span class="hc-badge">Most popular</span>{/if}
			</div>
			<div class="hc-domain">{opt.domain}</div>
			<div class="hc-label">{opt.label}</div>
			<div class="hc-note">{opt.note}</div>

			{#if opt.tags}
				<div class="hc-tags">
					{#each opt.tags as tag}
						<span class="hc-tag" class:hc-tag-accent={tag.recommended}>
							{tag.name}{#if tag.recommended}
								· Recommended{/if}
						</span>
					{/each}
				</div>
			{/if}

			{#if opt.methods}
				<div class="hc-methods">
					{#each opt.methods as m}
						<a href={m.href} target="_blank" rel="noopener" class="hc-method" title={m.name}>
							<img src={m.logo} alt={m.name} width="16" height="16" />
						</a>
					{/each}
				</div>
			{/if}
		</div>
	{/each}
</div>

<style>
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
		transition:
			box-shadow 0.2s,
			border-color 0.2s;
		box-shadow: 0 2px 8px color-mix(in srgb, var(--text) 4%, transparent);
	}

	.hosting-card.active {
		border-color: var(--accent);
		box-shadow:
			0 0 0 1px var(--accent),
			0 8px 24px color-mix(in srgb, var(--accent) 15%, transparent);
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

	.hc-tags {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 14px;
		padding-top: 14px;
		border-top: 1px solid var(--border);
	}

	.hc-tag {
		font-size: 11px;
		font-weight: 600;
		padding: 5px 10px;
		border-radius: 100px;
		border: 1px solid var(--border);
		color: var(--text-light);
		background: color-mix(in srgb, var(--text) 3%, var(--background));
	}

	.hc-tag.hc-tag-accent {
		color: var(--accent);
		border-color: color-mix(in srgb, var(--accent) 35%, transparent);
		background: color-mix(in srgb, var(--accent) 10%, transparent);
	}

	.hc-methods {
		display: flex;
		gap: 8px;
		margin-top: 14px;
		padding-top: 14px;
		border-top: 1px solid var(--border);
	}

	.hc-method {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 30px;
		height: 30px;
		border-radius: 8px;
		border: 1px solid var(--border);
		background: color-mix(in srgb, var(--text) 3%, var(--background));
		transition:
			border-color 0.15s,
			transform 0.15s;
	}

	.hc-method:hover {
		border-color: var(--accent);
		transform: translateY(-1px);
	}

	.hc-method img {
		display: block;
	}
</style>

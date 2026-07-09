<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import TrialChecks from '../@components/TrialChecks.svelte';
</script>

<section class="hero">
	<div class="hds-container inner">
		<div class="left">
			<div class="badge">Open-source</div>

			<h1>Blogging&nbsp;Platform.<br />Fast &amp; Simple.</h1>

			<p class="subtitle">
				Create, manage, and grow your blog without worrying about servers, databases, or
				infrastructure. Self-host or let us handle it.
			</p>

			<div class="buttons">
				<Button as="a" href="/console?signup" size="large">Start your blog</Button>
				<Button as="a" href="/customers" size="large" variant="outline">See customer blogs</Button>
			</div>

			<div class="trial-checks">
				<TrialChecks />
			</div>
		</div>

		<div class="right">
			<div class="browser">
				<div class="browser-bar">
					<div class="dots">
						<span class="dot red"></span>
						<span class="dot yellow"></span>
						<span class="dot green"></span>
					</div>
					<div class="url-bar">yourblog.com</div>
				</div>
				<div class="browser-content">
					<div class="mock-header">
						<div class="mock-logo"></div>
						<div class="mock-nav">
							<span></span><span></span><span></span>
						</div>
					</div>
					<div class="mock-post">
						<div class="mock-tag"></div>
						<div class="mock-title wide"></div>
						<div class="mock-title narrow"></div>
						<div class="mock-meta"></div>
						<div class="mock-image"></div>
						<div class="mock-text"></div>
						<div class="mock-text"></div>
						<div class="mock-text short"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<style>
	.hero {
		display: flex;
		align-items: center;
		min-height: calc(100vh - var(--header-height, 60px));
		padding: 60px 0 80px;
	}

	.inner {
		display: flex;
		align-items: center;
		gap: 100px;
		width: 1120px;
		max-width: 100%;
	}

	.left {
		flex: 1;
		min-width: 0;
	}

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
	}

	h1 {
		margin: 0;
		font-size: clamp(36px, 5vw, 56px);
		line-height: 1.1;
		letter-spacing: -0.02em;
		font-weight: 800;
	}

	.subtitle {
		margin: 0;
		margin-top: 20px;
		font-size: 1rem;
		line-height: 1.6;
		color: var(--text-light);
		max-width: 480px;
	}

	.buttons {
		display: flex;
		gap: 12px;
		margin-top: 36px;
		flex-wrap: wrap;
	}

	.trial-checks {
		margin-top: 24px;
	}

	/* Browser mockup */
	.right {
		flex: 1;
		min-width: 0;
	}

	.browser {
		border-radius: 20px;
		overflow: hidden;
		box-shadow:
			0 0 0 1px var(--border),
			0 20px 60px color-mix(in srgb, var(--text) 10%, transparent);
	}

	.browser-bar {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 10px 16px;
		background: var(--background-2, #f5f5f5);
		border-bottom: 1px solid var(--border);
	}

	.dots {
		display: flex;
		gap: 6px;
	}

	.dot {
		display: block;
		width: 12px;
		height: 12px;
		border-radius: 50%;
	}
	.dot.red {
		background: #ff5f57;
	}
	.dot.yellow {
		background: #febc2e;
	}
	.dot.green {
		background: #28c840;
	}

	.url-bar {
		flex: 1;
		text-align: center;
		font-size: 12px;
		color: var(--text-light);
		background: var(--background, white);
		border: 1px solid var(--border);
		border-radius: 6px;
		padding: 4px 10px;
		max-width: 220px;
		margin: 0 auto;
	}

	.browser-content {
		background: var(--background, white);
		padding: 0;
	}

	/* Mock blog content inside browser */
	.mock-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 14px 20px;
		border-bottom: 1px solid var(--border);
	}

	.mock-logo {
		width: 80px;
		height: 14px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 15%, transparent);
	}

	.mock-nav {
		display: flex;
		gap: 10px;
	}
	.mock-nav span {
		display: block;
		width: 40px;
		height: 10px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 10%, transparent);
	}

	.mock-post {
		padding: 24px 20px 28px;
	}

	.mock-tag {
		width: 60px;
		height: 20px;
		border-radius: 100px;
		background: color-mix(in srgb, var(--accent) 15%, transparent);
		margin-bottom: 14px;
	}

	.mock-title {
		height: 20px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 18%, transparent);
		margin-bottom: 10px;
	}
	.mock-title.wide {
		width: 90%;
	}
	.mock-title.narrow {
		width: 65%;
	}

	.mock-meta {
		width: 140px;
		height: 10px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 10%, transparent);
		margin-top: 14px;
		margin-bottom: 18px;
	}

	.mock-image {
		width: 100%;
		height: 160px;
		border-radius: 8px;
		background: linear-gradient(
			135deg,
			color-mix(in srgb, var(--accent) 20%, transparent),
			color-mix(in srgb, var(--accent) 5%, transparent)
		);
		margin-bottom: 18px;
	}

	.mock-text {
		height: 10px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 10%, transparent);
		margin-bottom: 10px;
		width: 100%;
	}
	.mock-text.short {
		width: 70%;
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
	}
</style>

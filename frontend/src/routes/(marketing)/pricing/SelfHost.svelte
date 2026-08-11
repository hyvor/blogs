<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconGithub from '@hyvor/icons/IconGithub';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';

	const bullets = [
		'Free & open-source — no license fees',
		'Deploy with Docker in a few commands',
		'Full ownership of your data & infrastructure',
		'No vendor lock-in — move to the cloud anytime'
	];
</script>

<section class="self-host hds-container">
	<div class="inner">
		<div class="text-col">
			<span class="eyebrow">Open Source</span>
			<h2>Prefer to self-host?</h2>
			<p>
				Hyvor Blogs is free and open-source. Deploy it on your own infrastructure and keep full
				control over your data — no subscription required.
			</p>

			<ul class="bullets">
				{#each bullets as b}
					<li><IconCheckCircleFill size={14} />{b}</li>
				{/each}
			</ul>

			<div class="buttons">
				<Button as="a" href="/hosting" size="large" variant="fill" color="accent">
					Learn about self-hosting
				</Button>
				<Button
					as="a"
					href="https://github.com/hyvor/blogs"
					target="_blank"
					rel="noopener"
					size="large"
					variant="outline"
					color="gray"
				>
					<IconGithub size={14} />
					View on GitHub
				</Button>
			</div>
		</div>

		<div class="visual-col">
			<div class="terminal">
				<div class="terminal-bar">
					<span class="dot red"></span>
					<span class="dot yellow"></span>
					<span class="dot green"></span>
				</div>
				<div class="terminal-body">
					<div class="line">
						<span class="prompt">$</span> git clone https://github.com/hyvor/blogs
					</div>
					<div class="line">
						<span class="prompt">$</span> cd blogs &amp;&amp; docker compose up -d
					</div>
					<div class="line output">✓ Blog running at http://localhost:8080</div>
				</div>
			</div>
		</div>
	</div>
</section>

<style>
	.self-host {
		margin-top: 60px;
	}

	.inner {
		display: flex;
		align-items: center;
		gap: 60px;
		padding: 50px 55px;
		border-radius: var(--box-radius);
		box-shadow: var(--box-shadow);
		background: linear-gradient(135deg, var(--accent-light-mid), var(--box-background) 60%);
	}

	.text-col,
	.visual-col {
		flex: 1;
		min-width: 0;
	}

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
		font-family: var(--font-serif);
		font-weight: 700;
		font-size: clamp(24px, 2.6vw, 30px);
		letter-spacing: -0.01em;
		line-height: 1.2;
		margin: 0 0 16px;
	}

	p {
		font-size: 15px;
		line-height: 1.6;
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
	}

	.bullets li {
		display: flex;
		align-items: center;
		gap: 10px;
		font-size: 14px;
	}

	.bullets li :global(svg) {
		color: var(--accent);
		flex-shrink: 0;
	}

	.buttons {
		display: flex;
		align-items: center;
		gap: 12px;
		flex-wrap: wrap;
	}

	.visual-col {
		display: flex;
		justify-content: center;
	}

	.terminal {
		width: 100%;
		max-width: 420px;
		border-radius: 12px;
		overflow: hidden;
		background: #16161a;
		box-shadow: var(--box-shadow);
	}

	.terminal-bar {
		display: flex;
		align-items: center;
		gap: 6px;
		padding: 10px 14px;
		background: #1f1f24;
	}

	.dot {
		width: 10px;
		height: 10px;
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

	.terminal-body {
		padding: 20px 18px;
		font-family: var(--font-mono, monospace);
		font-size: 13px;
		line-height: 2;
	}

	.line {
		color: #d4d4d8;
		white-space: nowrap;
	}

	.prompt {
		color: #7dd3fc;
		margin-right: 6px;
	}

	.line.output {
		color: #4ade80;
	}

	@media (max-width: 900px) {
		.inner {
			flex-direction: column;
			align-items: stretch;
			gap: 40px;
			padding: 36px 28px;
			text-align: center;
		}

		p {
			max-width: none;
			margin-left: auto;
			margin-right: auto;
		}

		.bullets {
			align-items: center;
		}

		.buttons {
			justify-content: center;
		}

		.visual-col {
			width: 100%;
		}
	}

	@media (max-width: 360px) {
		.line {
			white-space: normal;
		}
	}
</style>

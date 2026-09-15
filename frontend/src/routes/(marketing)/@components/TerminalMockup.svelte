<script lang="ts">
	interface Props {
		command?: string;
		output: string;
	}

	let { command = 'docker compose up -d', output }: Props = $props();
</script>

<div class="terminal">
	<div class="terminal-bar">
		<span class="dot red"></span>
		<span class="dot yellow"></span>
		<span class="dot green"></span>
	</div>
	<div class="terminal-body">
		<div class="line"><span class="prompt">$</span> {command}</div>
		<div class="line output">✓ {output}</div>
	</div>
</div>

<style>
	.terminal {
		width: 100%;
		max-width: 420px;
		margin: 0 auto;
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
		text-align: left;
	}

	.line {
		color: #d4d4d8;
		overflow-wrap: break-word;
	}

	.prompt {
		color: #7dd3fc;
		margin-right: 6px;
	}

	.line.output {
		color: #4ade80;
	}
</style>

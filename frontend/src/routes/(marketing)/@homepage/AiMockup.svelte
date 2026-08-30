<script lang="ts">
	import IconMagic from '@hyvor/icons/IconMagic';
	import IconStars from '@hyvor/icons/IconStars';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconCheckSquareFill from '@hyvor/icons/IconCheckSquareFill';

	interface Props {
		active?: number;
	}

	let { active = 0 }: Props = $props();

	const actions = [
		{ icon: IconMagic, label: 'Generate content' },
		{ icon: IconStars, label: 'Improvement suggestions' },
		{ icon: IconLink45deg, label: 'Bulk edit: add internal links' }
	];
</script>

<div class="ai-mockup">
	<div class="ai-editor">
		{#if active === 0}
			<div class="gen-prompt">
				<IconMagic size={13} />
				<span class="gen-prompt-text">Write a post about onboarding new users…</span>
			</div>
			<div class="editor-line title"></div>
			<div class="editor-line"></div>
			<div class="editor-line short"></div>
			<div class="editor-line generating"></div>
		{:else if active === 2}
			<div class="bulk-list">
				{#each { length: 4 } as _, i}
					<div class="bulk-row">
						<span class="bulk-check"><IconCheckSquareFill size={14} /></span>
						<span class="bulk-title" class:short={i === 2}></span>
						<span class="bulk-link-badge"><IconLink45deg size={11} /></span>
					</div>
				{/each}
			</div>
		{:else}
			<div class="editor-line title"></div>
			<div class="editor-line"></div>
			<div class="editor-line short"></div>

			<div class="editor-line highlighted">
				<span class="highlight-fill"></span>
			</div>

			<div class="ai-hint">
				<span class="ai-hint-icon"><IconStars size={13} /></span>
				<span class="ai-hint-text">Improve readability of this sentence</span>
				<span class="ai-accept">Accept</span>
			</div>

			<div class="editor-line"></div>
			<div class="editor-line short"></div>
		{/if}
	</div>

	<div class="ai-actions">
		{#each actions as a, i}
			<div class="ai-chip" class:active={active === i}>
				<span class="ai-chip-icon"><a.icon size={13} /></span>
				{a.label}
			</div>
		{/each}
	</div>
</div>

<style>
	.ai-mockup {
		border-radius: 20px;
		border: 1px solid var(--border);
		overflow: hidden;
		background: var(--background);
		box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent);
	}

	.ai-editor {
		display: flex;
		flex-direction: column;
		gap: 10px;
		padding: 24px 24px 20px;
		min-height: 154px;
	}

	.editor-line {
		height: 10px;
		border-radius: 4px;
		width: 100%;
		background: color-mix(in srgb, var(--text) 10%, transparent);
	}

	.editor-line.title {
		height: 15px;
		width: 62%;
		background: color-mix(in srgb, var(--text) 22%, transparent);
	}

	.editor-line.short {
		width: 78%;
	}

	.editor-line.highlighted {
		position: relative;
		background: color-mix(in srgb, var(--accent) 12%, transparent);
		overflow: hidden;
	}

	.highlight-fill {
		position: absolute;
		inset: 0;
		border-radius: 4px;
		border: 1px dashed color-mix(in srgb, var(--accent) 50%, transparent);
	}

	.ai-hint {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 8px 12px;
		margin: 2px 0 4px;
		border-radius: 10px;
		background: color-mix(in srgb, var(--accent) 8%, var(--background));
		border: 1px solid color-mix(in srgb, var(--accent) 25%, transparent);
	}

	.ai-hint-icon {
		flex-shrink: 0;
		display: flex;
		color: var(--accent);
	}

	.ai-hint-text {
		flex: 1;
		font-size: 12px;
		font-weight: 500;
		color: var(--text);
	}

	.ai-accept {
		flex-shrink: 0;
		font-size: 11px;
		font-weight: 700;
		color: var(--accent-text, #fff);
		background: var(--accent);
		padding: 4px 10px;
		border-radius: 100px;
	}

	.gen-prompt {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 9px 12px;
		border-radius: 10px;
		margin-bottom: 4px;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 10%, var(--background));
		border: 1px solid color-mix(in srgb, var(--accent) 25%, transparent);
	}

	.gen-prompt-text {
		font-size: 12px;
		font-weight: 600;
	}

	.editor-line.generating {
		position: relative;
		width: 45%;
		overflow: hidden;
		background: color-mix(in srgb, var(--accent) 16%, transparent);
	}

	.editor-line.generating::after {
		content: '';
		position: absolute;
		inset: 0;
		background: linear-gradient(
			90deg,
			transparent,
			color-mix(in srgb, var(--accent) 45%, transparent),
			transparent
		);
		animation: shimmer 1.3s ease-in-out infinite;
	}

	@keyframes shimmer {
		0% {
			transform: translateX(-100%);
		}
		100% {
			transform: translateX(100%);
		}
	}

	.bulk-list {
		display: flex;
		flex-direction: column;
		gap: 11px;
	}

	.bulk-row {
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.bulk-check {
		flex-shrink: 0;
		display: flex;
		color: var(--accent);
	}

	.bulk-title {
		flex: 1;
		height: 10px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 12%, transparent);
	}

	.bulk-title.short {
		width: 65%;
		flex: none;
	}

	.bulk-link-badge {
		flex-shrink: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 22px;
		height: 22px;
		border-radius: 50%;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 15%, transparent);
	}

	.ai-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		padding: 16px 20px 20px;
		border-top: 1px solid var(--border);
	}

	.ai-chip {
		display: inline-flex;
		align-items: center;
		gap: 7px;
		font-size: 12px;
		font-weight: 600;
		padding: 6px 12px 6px 8px;
		border-radius: 100px;
		border: 1px solid var(--border);
		color: var(--text);
		background: color-mix(in srgb, var(--text) 3%, var(--background));
		transition:
			border-color 0.2s,
			background-color 0.2s;
	}

	.ai-chip.active {
		border-color: color-mix(in srgb, var(--accent) 40%, transparent);
		background: color-mix(in srgb, var(--accent) 10%, transparent);
	}

	.ai-chip-icon {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 20px;
		height: 20px;
		border-radius: 50%;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 15%, transparent);
		flex-shrink: 0;
	}
</style>

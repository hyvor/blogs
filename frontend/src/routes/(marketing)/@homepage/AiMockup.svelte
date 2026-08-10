<script lang="ts">
	import IconMagic from '@hyvor/icons/IconMagic';
	import IconStars from '@hyvor/icons/IconStars';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';

	const actions = [
		{ icon: IconMagic, label: 'Generate content' },
		{ icon: IconStars, label: 'Improvement suggestions' },
		{ icon: IconLink45deg, label: 'Bulk edit: add internal links' }
	];
</script>

<div class="ai-mockup">
	<div class="ai-editor">
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
	</div>

	<div class="ai-actions">
		{#each actions as a}
			<div class="ai-chip">
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

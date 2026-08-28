<script lang="ts">
	import IconCursorFill from '@hyvor/icons/IconCursorFill';
	import IconChatSquareTextFill from '@hyvor/icons/IconChatSquareTextFill';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconXLg from '@hyvor/icons/IconXLg';

	interface Props {
		active?: number;
	}

	let { active = 0 }: Props = $props();

	// two collaborators, each with their own cursor/label color — same idea
	// as Google Docs / Figma style presence cursors
	const collaborators = [
		{ name: 'Priya', color: 'var(--blue)' },
		{ name: 'Alex', color: 'var(--orange)' }
	];
	const [priya, alex] = collaborators as [(typeof collaborators)[0], (typeof collaborators)[0]];

	const actions = [
		{ icon: IconCursorFill, label: 'Real-time collaboration' },
		{ icon: IconChatSquareTextFill, label: 'Suggestion mode' }
	];
</script>

<div class="collab-mockup">
	<div class="collab-editor">
		{#if active === 0}
			<!-- Real-time collaboration: a vertical text caret per collaborator,
			     each with its own colored name tag sitting right above it —
			     the real Console UI, not a mouse pointer -->
			<div class="editor-line title"></div>
			<div class="editor-line"></div>

			<div class="cursor-line">
				<div class="editor-line inline"></div>
				<span class="caret" style="--c: {priya.color}; left: 46%">
					<span class="caret-name">{priya.name}</span>
					<span class="caret-bar"></span>
				</span>
			</div>

			<div class="editor-line short"></div>

			<div class="cursor-line">
				<div class="editor-line"></div>
				<span class="caret" style="--c: {alex.color}; left: 72%">
					<span class="caret-name">{alex.name}</span>
					<span class="caret-bar"></span>
				</span>
			</div>

			<div class="editor-line short"></div>

			<div class="presence-row">
				{#each collaborators as c}
					<span class="presence-avatar" style="--c: {c.color}">{c.name[0]}</span>
				{/each}
				<span class="presence-text">2 people editing now</span>
			</div>
		{:else}
			<!-- Suggestion mode: the comment/suggestion card sits in a left
			     margin next to the proposed edit, like the real Console UI,
			     rather than stacked underneath it -->
			<div class="suggestion-layout">
				<div class="suggestion-sidebar">
					<div class="comment-card">
						<div class="comment-card-head">
							<span class="comment-avatar" style="--c: {priya.color}">{priya.name[0]}</span>
							<span class="comment-name">{priya.name}</span>
						</div>
						<span class="comment-text">Punchier phrase here?</span>
						<div class="comment-buttons">
							<span class="comment-action reject"><IconXLg size={9} /></span>
							<span class="comment-action accept"><IconCheck size={11} /></span>
						</div>
					</div>
				</div>

				<div class="suggestion-content">
					<div class="editor-line title"></div>
					<div class="editor-line"></div>
					<div class="suggestion-line">
						<span class="text-removed">reach more readers</span>
						<span class="text-inserted">grow your audience</span>
					</div>
					<div class="editor-line short"></div>
					<div class="editor-line"></div>
				</div>
			</div>
		{/if}
	</div>

	<div class="collab-actions">
		{#each actions as a, i}
			<div class="collab-chip" class:active={active === i}>
				<span class="collab-chip-icon"><a.icon size={13} /></span>
				{a.label}
			</div>
		{/each}
	</div>
</div>

<style>
	.collab-mockup {
		border-radius: 20px;
		border: 1px solid var(--border);
		overflow: hidden;
		background: var(--background);
		box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent);
	}

	.collab-editor {
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

	.editor-line.inline {
		width: 55%;
	}

	/* Real-time collaboration */
	.cursor-line {
		position: relative;
		/* room above the line for the name tag riding on top of the caret */
		margin-top: 16px;
	}

	.caret {
		position: absolute;
		bottom: -3px;
		display: flex;
		flex-direction: column;
		align-items: flex-start;
	}

	.caret-name {
		font-size: 10px;
		font-weight: 700;
		line-height: 1;
		color: var(--accent-text, #fff);
		background: var(--c);
		padding: 2px 6px;
		border-radius: 4px 4px 4px 2px;
		white-space: nowrap;
		margin-bottom: 2px;
	}

	.caret-bar {
		width: 2px;
		height: 16px;
		border-radius: 1px;
		background: var(--c);
	}

	.presence-row {
		display: flex;
		align-items: center;
		gap: 6px;
		margin-top: 4px;
	}

	.presence-avatar {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 22px;
		height: 22px;
		border-radius: 50%;
		font-size: 10px;
		font-weight: 700;
		color: var(--accent-text, #fff);
		background: var(--c);
		border: 2px solid var(--background);
		margin-left: -8px;
	}

	.presence-avatar:first-child {
		margin-left: 0;
	}

	.presence-text {
		margin-left: 4px;
		font-size: 11px;
		font-weight: 500;
		color: var(--text-light);
	}

	/* Suggestion mode */
	.suggestion-layout {
		display: flex;
		gap: 14px;
		align-items: flex-start;
	}

	.suggestion-sidebar {
		flex: 0 0 112px;
		width: 112px;
		/* nudge the card down so it sits roughly beside the suggested line,
		   not the title */
		margin-top: 40px;
	}

	.suggestion-content {
		flex: 1;
		min-width: 0;
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.suggestion-line {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 6px;
		font-size: 12px;
	}

	.text-removed {
		color: var(--text-light);
		text-decoration: line-through;
		text-decoration-color: color-mix(in srgb, var(--red, #e5484d) 70%, transparent);
		opacity: 0.7;
	}

	.text-inserted {
		font-weight: 600;
		color: var(--accent);
		text-decoration: underline;
		text-decoration-style: wavy;
		text-decoration-color: color-mix(in srgb, var(--accent) 45%, transparent);
	}

	.comment-card {
		position: relative;
		display: flex;
		flex-direction: column;
		gap: 6px;
		padding: 10px;
		border-radius: 10px;
		background: color-mix(in srgb, var(--accent) 6%, var(--background));
		border: 1px solid color-mix(in srgb, var(--accent) 20%, transparent);
	}

	/* a small pointer on the card's right edge, connecting it to the
	   suggested line it belongs to — a real margin-comment detail */
	.comment-card::after {
		content: '';
		position: absolute;
		top: 16px;
		right: -5px;
		width: 9px;
		height: 9px;
		background: inherit;
		border-right: 1px solid color-mix(in srgb, var(--accent) 20%, transparent);
		border-bottom: 1px solid color-mix(in srgb, var(--accent) 20%, transparent);
		border-bottom-right-radius: 2px;
		transform: rotate(-45deg);
	}

	.comment-card-head {
		display: flex;
		align-items: center;
		gap: 6px;
	}

	.comment-avatar {
		flex-shrink: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 20px;
		height: 20px;
		border-radius: 50%;
		font-size: 9px;
		font-weight: 700;
		color: var(--accent-text, #fff);
		background: var(--c);
	}

	.comment-name {
		font-size: 11px;
		font-weight: 700;
		color: var(--text);
	}

	.comment-text {
		font-size: 11px;
		line-height: 1.4;
		color: var(--text-light);
	}

	.comment-buttons {
		display: flex;
		gap: 6px;
		margin-top: 2px;
	}

	.comment-action {
		flex-shrink: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 20px;
		height: 20px;
		border-radius: 50%;
	}

	.comment-action.reject {
		color: var(--text-light);
		background: color-mix(in srgb, var(--text) 8%, transparent);
	}

	.comment-action.accept {
		color: var(--accent-text, #fff);
		background: var(--accent);
	}

	.collab-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		padding: 16px 20px 20px;
		border-top: 1px solid var(--border);
	}

	.collab-chip {
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

	.collab-chip.active {
		border-color: color-mix(in srgb, var(--accent) 40%, transparent);
		background: color-mix(in srgb, var(--accent) 10%, transparent);
	}

	.collab-chip-icon {
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

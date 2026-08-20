<script lang="ts">
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconX from '@hyvor/icons/IconX';

	interface Props {
		active?: number;
	}

	let { active = 0 }: Props = $props();

	const alex = { initials: 'AL', color: '#6c5ce7', name: 'Alex' };
	const mia = { initials: 'MI', color: '#00b894', name: 'Mia' };
	const collaborators = [alex, mia];
</script>

<div class="collab-mockup">
	<div class="collab-editor">
		{#if active === 0}
			<!-- Live collaboration: two people editing the same post at once -->
			<div class="collab-bar">
				<div class="avatar-stack">
					{#each collaborators as c}
						<span class="avatar" style="background:{c.color}">{c.initials}</span>
					{/each}
				</div>
				<span class="collab-status"><span class="live-dot"></span>2 editing now</span>
			</div>

			<div class="editor-line title"></div>
			<div class="editor-line"></div>
			<div class="editor-line cursor-line" style="--cursor-color: {alex.color}">
				<span class="line-fill"></span>
				<span class="live-cursor" style="--cursor-color: {alex.color}">
					<span class="cursor-tag">{alex.name}</span>
				</span>
			</div>
			<div class="editor-line short cursor-line" style="--cursor-color: {mia.color}">
				<span class="line-fill"></span>
				<span class="live-cursor end" style="--cursor-color: {mia.color}">
					<span class="cursor-tag">{mia.name}</span>
				</span>
			</div>
			<div class="editor-line"></div>
		{:else}
			<!-- Suggestion mode: a proposed edit waiting for accept/reject -->
			<div class="editor-line title"></div>
			<div class="editor-line"></div>

			<p class="suggestion-para">
				<span class="para-fill"></span>
				<span class="sugg-del">grew fast</span>
				<span class="sugg-ins">grew 40% quarter over quarter</span>
				<span class="para-fill short"></span>
			</p>

			<div class="suggestion-card">
				<span class="sugg-avatar" style="background:{alex.color}">{alex.initials}</span>
				<span class="sugg-text">Suggested a more specific stat</span>
				<span class="sugg-actions">
					<span class="sugg-btn reject"><IconX size={12} /></span>
					<span class="sugg-btn accept"><IconCheck size={12} /></span>
				</span>
			</div>

			<div class="editor-line short"></div>
		{/if}
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
		padding: 24px;
		min-height: 200px;
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

	/* Live collaboration */
	.collab-bar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 4px;
	}

	.avatar-stack {
		display: flex;
	}

	.avatar {
		width: 26px;
		height: 26px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 10px;
		font-weight: 700;
		color: #fff;
		border: 2px solid var(--background);
	}

	.avatar-stack .avatar:not(:first-child) {
		margin-left: -8px;
	}

	.collab-status {
		display: flex;
		align-items: center;
		gap: 6px;
		font-size: 11px;
		font-weight: 600;
		color: var(--text-light);
	}

	.live-dot {
		width: 7px;
		height: 7px;
		border-radius: 50%;
		background: #00b894;
		animation: pulse-dot 1.6s ease-in-out infinite;
	}

	@keyframes pulse-dot {
		0%,
		100% {
			opacity: 1;
		}
		50% {
			opacity: 0.35;
		}
	}

	.cursor-line {
		position: relative;
		overflow: visible;
		background: transparent;
	}

	.line-fill {
		display: block;
		height: 100%;
		width: 100%;
		border-radius: 4px;
		background: color-mix(in srgb, var(--cursor-color) 16%, transparent);
	}

	.live-cursor {
		position: absolute;
		top: -2px;
		right: 22%;
		width: 2px;
		height: 14px;
		background: var(--cursor-color);
		animation: caret-blink 1s step-start infinite;
	}

	.live-cursor.end {
		right: 8%;
	}

	.cursor-tag {
		position: absolute;
		top: -20px;
		left: 50%;
		transform: translateX(-50%);
		white-space: nowrap;
		font-size: 9px;
		font-weight: 700;
		color: #fff;
		padding: 2px 6px;
		border-radius: 4px;
		background: var(--cursor-color);
	}

	@keyframes caret-blink {
		50% {
			opacity: 0;
		}
	}

	/* Suggestion mode */
	.suggestion-para {
		margin: 0;
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 6px;
		line-height: 1.8;
	}

	.para-fill {
		height: 10px;
		width: 30%;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 10%, transparent);
	}

	.para-fill.short {
		width: 45%;
	}

	.sugg-del {
		font-size: 12px;
		font-weight: 600;
		color: var(--text-light);
		text-decoration: line-through;
		text-decoration-color: #e74c3c;
		background: color-mix(in srgb, #e74c3c 10%, transparent);
		padding: 2px 6px;
		border-radius: 4px;
	}

	.sugg-ins {
		font-size: 12px;
		font-weight: 600;
		color: #00b894;
		background: color-mix(in srgb, #00b894 12%, transparent);
		padding: 2px 6px;
		border-radius: 4px;
		text-decoration: underline;
		text-decoration-color: #00b894;
	}

	.suggestion-card {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 8px 10px;
		border-radius: 10px;
		background: color-mix(in srgb, var(--text) 3%, var(--background));
		border: 1px solid var(--border);
	}

	.sugg-avatar {
		flex-shrink: 0;
		width: 20px;
		height: 20px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 8px;
		font-weight: 700;
		color: #fff;
	}

	.sugg-text {
		flex: 1;
		font-size: 11px;
		font-weight: 500;
		color: var(--text);
	}

	.sugg-actions {
		display: flex;
		gap: 6px;
		flex-shrink: 0;
	}

	.sugg-btn {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 20px;
		height: 20px;
		border-radius: 50%;
	}

	.sugg-btn.reject {
		color: #e74c3c;
		background: color-mix(in srgb, #e74c3c 12%, transparent);
	}

	.sugg-btn.accept {
		color: #00b894;
		background: color-mix(in srgb, #00b894 14%, transparent);
	}
</style>

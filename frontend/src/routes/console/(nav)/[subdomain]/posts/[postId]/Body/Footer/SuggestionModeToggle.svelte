<script lang="ts">
	import { setSuggestionMode } from '@hyvor/richtext';
	import { postEditor, postSuggestionModeStore } from '../../../postStore';

	function setMode(mode: 'editing' | 'suggesting') {
		if ($postSuggestionModeStore === mode) return;

		postSuggestionModeStore.set(mode);

		const view = $postEditor?.getView();
		if (view) {
			setSuggestionMode(view, mode);
		}
	}
</script>

<div class="suggestion-mode-toggle">
	<button
		type="button"
		class:active={$postSuggestionModeStore === 'editing'}
		onclick={() => setMode('editing')}
	>
		Editing
	</button>
	<button
		type="button"
		class:active={$postSuggestionModeStore === 'suggesting'}
		onclick={() => setMode('suggesting')}
	>
		Suggesting
	</button>
</div>

<style>
	.suggestion-mode-toggle {
		display: inline-flex;
		border: 1px solid var(--border);
		border-radius: 6px;
		overflow: hidden;
	}
	.suggestion-mode-toggle button {
		border: none;
		background: transparent;
		font-size: 12px;
		font-weight: 600;
		color: var(--text-light);
		padding: 4px 10px;
		cursor: pointer;
	}
	.suggestion-mode-toggle button + button {
		border-left: 1px solid var(--border);
	}
	.suggestion-mode-toggle button.active {
		background: var(--hover);
		color: var(--text);
	}
</style>

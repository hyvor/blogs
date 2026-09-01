<script lang="ts">
	import { Button, ButtonGroup } from '@hyvor/design/components';
	import { postEditor, postSuggestionModeStore } from '../../../postStore';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	function setMode(mode: 'editing' | 'suggesting') {
		if ($postSuggestionModeStore === mode) return;
		postSuggestionModeStore.set(mode);
		$postEditor?.suggestions.setMode(mode);
	}
</script>

<div class="suggestion-mode-toggle">
	<ButtonGroup>
		<Button
			color={$postSuggestionModeStore === 'editing' ? 'gray' : 'input'}
			size="x-small"
			onclick={() => setMode('editing')}
		>
			{i18n.t('console.postEditor.suggestionMode.editing')}
		</Button>
		<Button
			color={$postSuggestionModeStore === 'suggesting' ? 'blue' : 'input'}
			size="x-small"
			onclick={() => setMode('suggesting')}
		>
			{i18n.t('console.postEditor.suggestionMode.suggesting')}
		</Button>
	</ButtonGroup>
</div>

{#if $postSuggestionModeStore === 'suggesting'}
	<div class="suggestion-mode-info">
		{i18n.t('console.postEditor.suggestionMode.suggestingInfo')}
	</div>
{/if}

<style>
	.suggestion-mode-info {
		margin-left: 4px;
		font-size: 12px;
		color: var(--blue-dark);
		font-weight: 600;
	}
</style>

<script lang="ts">
	import {
		SplitControl,
		Switch,
		Table,
		TableRow,
		TableCell,
		Tooltip
	} from '@hyvor/design/components';
	import IconCheck from '@hyvor/icons/IconCheck';
	import { blogStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import { getConfig } from '../../../../lib/config';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import { getI18n } from '../../../../lib/i18n';
	import { onMount } from 'svelte';
	import { redirectIfCant } from '../../../../lib/scope.svelte';

	onMount(() => {
		redirectIfCant('blog.write');
	});

	const models = getConfig().ai_models;

	function handleAiModelChange(value: string) {
		updateBlogStore({ ai_model: value });
	}

	function handleAiModelKeydown(e: KeyboardEvent, value: string) {
		if (e.key === 'Enter' || e.key === ' ') {
			e.preventDefault();
			handleAiModelChange(value);
		}
	}

	function handleAiTranslationChange(e: any) {
		updateBlogStore({ ai_translation_enabled: e.target.checked });
	}

	function handleAiAgentChange(e: any) {
		updateBlogStore({ ai_agent: e.target.checked });
	}

	const i18n = getI18n();
</script>

<BlogSettingsSave keys={['ai_model', 'ai_translation_enabled', 'ai_agent']} />

<div class="settings">
	<SplitControl
		label="Preferred Model"
		caption="Which AI model should be used for AI-powered features on this blog?"
		column
	>
		<Table columns="28px 1fr 1fr 2fr" style="bordered" hover>
			<TableRow head>
				<TableCell>&nbsp;</TableCell>
				<TableCell>Model</TableCell>
				<TableCell>Provider</TableCell>
				<TableCell>
					<span class="quota-usage-label">
						Quota Usage

						<Tooltip
							text="Models with higher quota usage consume more of your organization's AI quota."
						>
							<IconInfoCircle size={14} />
						</Tooltip>
					</span>
				</TableCell>
			</TableRow>
			{#each models as model}
				<TableRow
					class="model-row"
					tabindex={0}
					onclick={() => handleAiModelChange(model.value)}
					onkeydown={(e: KeyboardEvent) => handleAiModelKeydown(e, model.value)}
					aria-selected={model.value === $blogStore.ai_model}
				>
					<TableCell>
						{#if model.value === $blogStore.ai_model}
							<IconCheck size={14} />
						{:else}
							&nbsp;
						{/if}
					</TableCell>
					<TableCell>{model.value}</TableCell>
					<TableCell>{model.provider}</TableCell>
					<TableCell>
						<div class="usage-bar">
							<div class="usage-bar-fill" style:width={model.usage_percent + '%'}></div>
						</div>
					</TableCell>
				</TableRow>
			{/each}
		</Table>
	</SplitControl>

	<SplitControl
		label={i18n.t('console.settings.ai.translation')}
		caption={i18n.t('console.settings.ai.translationCaption')}
	>
		<Switch checked={$blogStore.ai_translation_enabled} on:change={handleAiTranslationChange} />
	</SplitControl>

	<SplitControl label="AI Agent" caption="Enable AI-powered content generation features.">
		<Switch checked={$blogStore.ai_agent} on:change={handleAiAgentChange} />
	</SplitControl>
</div>

<style>
	.settings {
		flex: 1;
		overflow: auto;
		padding: 25px 30px;
	}
	:global(.model-row) {
		cursor: pointer;
	}
	.usage-bar {
		width: 200px;
		max-width: 100%;
		height: 8px;
		background: var(--accent-light);
		border-radius: 20px;
		overflow: hidden;
	}
	.usage-bar-fill {
		height: 100%;
		border-radius: 20px;
		background: var(--accent);
	}
	.quota-usage-label {
		display: flex;
		align-items: center;
		gap: 4px;
	}
</style>

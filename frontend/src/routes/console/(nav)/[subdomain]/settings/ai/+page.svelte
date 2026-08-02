<script lang="ts">
	import { InputGroup, Radio, SplitControl, Switch } from '@hyvor/design/components';
	import { blogStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';

	const providers: { value: 'mistral' | 'openai' | 'anthropic'; label: string; model: string }[] =
		[
			{ value: 'mistral', label: 'Mistral', model: 'mistral-medium-3.5' },
			{ value: 'openai', label: 'OpenAI', model: 'gpt-5.6-terra' },
			{ value: 'anthropic', label: 'Anthropic', model: 'claude-sonnet-5' }
		];

	function handleAiProviderChange(value: 'mistral' | 'openai' | 'anthropic') {
		updateBlogStore({ ai_provider: value });
	}

	function handleAiTranslationChange(e: any) {
		updateBlogStore({ ai_translation_enabled: e.target.checked });
	}

	function handleAiGenerationChange(e: any) {
		updateBlogStore({ ai_generation_enabled: e.target.checked });
	}
</script>

<BlogSettingsSave keys={['ai_provider', 'ai_translation_enabled', 'ai_generation_enabled']} />

<div class="settings">
	<SplitControl
		label="Preferred AI Provider"
		caption="Which AI provider should be used for AI-powered features on this blog?"
	>
		<InputGroup>
			{#each providers as provider}
				<Radio
					value={provider.value}
					group={$blogStore.ai_provider}
					on:change={() => handleAiProviderChange(provider.value)}
				>
					{provider.label}&nbsp;<span class="model">({provider.model})</span>
				</Radio>
			{/each}
		</InputGroup>
	</SplitControl>

	<SplitControl label="AI Translation" caption="Enable AI-powered translation features.">
		<Switch checked={$blogStore.ai_translation_enabled} on:change={handleAiTranslationChange} />
	</SplitControl>

	<SplitControl label="AI Generation" caption="Enable AI-powered content generation features.">
		<Switch checked={$blogStore.ai_generation_enabled} on:change={handleAiGenerationChange} />
	</SplitControl>
</div>

<style>
	.settings {
		flex: 1;
		overflow: auto;
		padding: 25px 30px;
	}
	.model {
		color: var(--text-light);
		font-size: 0.9em;
	}
</style>

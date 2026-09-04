<script lang="ts">
	import { FormControl, InputGroup, Radio, SplitControl, Switch } from '@hyvor/design/components';
	import { blogStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import CodemirrorEditor from '../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import { getI18n } from '../../../../lib/i18n';
	import { onMount } from 'svelte';
	import { redirectIfCant } from '../../../../lib/scope.svelte';

	onMount(() => {
		redirectIfCant('blog.write');
	});

	const i18n = getI18n();

	function handleColorModeChange(value: 'light' | 'dark' | 'both') {
		updateBlogStore({ color_modes: value });
	}

	function handleColorModeDefaultChange(value: 'light' | 'dark' | 'os') {
		updateBlogStore({ color_mode_default: value });
	}
</script>

<BlogSettingsSave keys={['color_modes', 'color_mode_default']} />

<div class="settings">
	<SplitControl
		label={i18n.t('console.settings.colorMode.modes')}
		caption={i18n.t('console.settings.colorMode.modesCaption')}
	>
		<InputGroup>
			<Radio
				value="light"
				group={$blogStore.color_modes}
				on:change={(e) => handleColorModeChange('light')}
			>
				{i18n.t('console.settings.colorMode.light')}
			</Radio>

			<Radio
				value="dark"
				group={$blogStore.color_modes}
				on:change={(e) => handleColorModeChange('dark')}
			>
				{i18n.t('console.settings.colorMode.dark')}
			</Radio>

			<Radio
				value="both"
				group={$blogStore.color_modes}
				on:change={(e) => handleColorModeChange('both')}
			>
				{i18n.t('console.settings.colorMode.both')}
			</Radio>
		</InputGroup>
	</SplitControl>

	<SplitControl
		label={i18n.t('console.settings.colorMode.default')}
		caption={i18n.t('console.settings.colorMode.defaultCaption')}
	>
		<InputGroup>
			<Radio
				value="os"
				group={$blogStore.color_mode_default}
				on:change={(e) => handleColorModeDefaultChange('os')}
			>
				{i18n.t('console.settings.colorMode.osPreference')}
			</Radio>

			<Radio
				value="light"
				group={$blogStore.color_mode_default}
				on:change={(e) => handleColorModeDefaultChange('light')}
			>
				{i18n.t('console.settings.colorMode.light')}
			</Radio>

			<Radio
				value="dark"
				group={$blogStore.color_mode_default}
				on:change={(e) => handleColorModeDefaultChange('dark')}
			>
				{i18n.t('console.settings.colorMode.dark')}
			</Radio>
		</InputGroup>
	</SplitControl>
</div>

<style>
	.settings {
		flex: 1;
		overflow: auto;
		padding: 25px 30px;
	}
</style>

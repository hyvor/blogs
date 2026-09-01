<script lang="ts">
	import ConfigUiPreview from './Preview/ConfigUiPreview.svelte';
	import { configModeStore } from '../themeStore';
	import { type CodeMirrorMode } from '../../../../lib/components/CodemirrorEditor/codemirror';
	import { selectedThemeFileStore } from '../themeStore';
	import TextEditor from './Preview/TextEditor.svelte';
	import AssetImage from './Preview/AssetImage.svelte';
	import { IconMessage, Link } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconEyeSlash from '@hyvor/icons/IconEyeSlash';

	import { blogStore } from '../../../../lib/stores/blogStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	let currentFile = $derived($selectedThemeFileStore!);
	let ext = $derived((currentFile.name.split('.').pop() || '') as CodeMirrorMode);

	const textExtensions = ['scss', 'twig', 'js', 'yaml'];
	const imageExtensions = [
		'png',
		'jpg',
		'jpeg',
		'jfif',
		'pjpeg',
		'pjp',
		'gif',
		'apng',
		'avif',
		'svg',
		'webp'
	];
</script>

{#if currentFile.folder === null && currentFile.name === 'config.yaml' && $configModeStore === 'ui'}
	<ConfigUiPreview file={currentFile} />
{:else if textExtensions.includes(ext)}
	<TextEditor file={currentFile} {ext} />
{:else if currentFile.folder === 'assets' && imageExtensions.includes(ext)}
	<AssetImage file={currentFile} />
{:else}
	<IconMessage>
		{#snippet icon()}
			<IconEyeSlash size={60} />
		{/snippet}
		{#snippet message()}
			<div>
				{i18n.t('console.theme.noPreview')}

				{#if currentFile.folder === 'assets'}
					<div style="margin-top:5px;font-size:14px;">
						<Link
							href={`${$blogStore.url}/assets/${currentFile.name}`}
							target="_blank"
							rel="noopener noreferrer"
						>
							Open in new tab {#snippet end()}
								<IconBoxArrowUpRight size={14} />
							{/snippet}
						</Link>
					</div>
				{/if}
			</div>
		{/snippet}
	</IconMessage>
{/if}

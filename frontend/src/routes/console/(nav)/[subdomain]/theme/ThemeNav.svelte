<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { setThemeFiles } from './themeStore';
	import Folder from './Folder.svelte';
	import Upload from './Upload.svelte';
	import Download from './Download.svelte';
	import ChangeTheme from './ChangeTheme/ChangeTheme.svelte';
	import { loadThemeFiles } from './themeActions';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();

	let isLoading = $state(true);

	onMount(() => {
		loadThemeFiles().then((res) => {
			setThemeFiles(res);
			isLoading = false;
		});
	});
</script>

<div class="theme-nav">
	<div class="title">
		<span>{i18n.t('console.nav.theme')}</span>
		<ChangeTheme />
	</div>

	<div class="folders">
		{#if isLoading}
			<Loader full />
		{:else}
			<Folder name="templates" />
			<Folder name="styles" />
			<Folder name="assets" />
			<Folder name="lang" />
			<Folder name={null} />
		{/if}
	</div>

	<div class="theme-bottom">
		<Upload />
		<Download />
	</div>
</div>

<style>
	.theme-nav {
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.title {
		padding: 20px;
		text-align: center;
		border-bottom: 1px solid var(--border);
	}
	.title span {
		margin-right: 5px;
	}

	.folders {
		flex: 1;
		overflow: auto;
		padding: 10px 30px;
	}

	.theme-bottom {
		padding: 20px;
		border-top: 1px solid var(--border);
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 8px;
	}
</style>

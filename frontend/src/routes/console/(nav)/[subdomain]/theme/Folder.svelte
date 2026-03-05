<script lang="ts">
	import IconCaretDownFill from '@hyvor/icons/IconCaretDownFill';
	import IconCaretRightFill from '@hyvor/icons/IconCaretRightFill';

	import type { ThemeFolder } from '../../../lib/types';
	import { selectedThemeFileIdStore, themeFilesStore } from './themeStore';
	import NewFileCreator from './Editor/NewFileCreator.svelte';
	interface Props {
		name: ThemeFolder;
	}

	let { name }: Props = $props();

	let files = $derived($themeFilesStore);
	let filesOfFolder = $derived(files.filter((file) => file.folder === name));

	let open = $state(false);
</script>

<div class="folder {name || 'root'}">
	{#if name}
		<button class="folder-name" onclick={() => (open = !open)}>
			<span class="fold-icon">
				{#if open}
					<IconCaretDownFill size={10} />
				{:else}
					<IconCaretRightFill size={10} />
				{/if}
			</span>

			<span class="name">{name}</span>
		</button>
	{/if}

	<div class={'folder-files' + (open || name === null ? ' unfolded' : '')}>
		{#each filesOfFolder as file (file.id)}
			<button
				class={'file' + ($selectedThemeFileIdStore === file.id ? ' active' : '')}
				onclick={() => selectedThemeFileIdStore.set(file.id)}
			>
				{file.name}
			</button>
		{/each}

		<NewFileCreator folder={name} />
	</div>
</div>

<style lang="scss">
	.folder {
		margin-bottom: 2px;
		.folder-name {
			display: block;
			width: 100%;
			font-weight: 600;
			cursor: pointer;
			padding: 4px;
			display: flex;
			align-items: center;
			user-select: none;
			font-size: 14px;
			.fold-icon {
				margin-right: 5px;
				margin-top: -2px;
			}
		}
		.folder-files {
			overflow: hidden;
			height: 0;
			padding: 0;
			border-left: 1px solid var(--accent-light);
			&.unfolded {
				padding: 5px 15px 0 10px;
				margin-left: 8.5px;
				padding-top: 0;
				height: initial;
			}
		}

		&.root {
			.folder-files {
				padding-left: 0;
				border-left: none;
				margin-left: 0;
			}
		}
	}

	.file {
		display: block;
		padding: 4px 15px;
		cursor: pointer;
		border-radius: 20px;
		font-size: 14px;
		width: 100%;
		text-align: left;
		&:hover {
			background-color: var(--hover);
		}
		&.active {
			background-color: var(--accent-light-mid);
		}
	}
</style>

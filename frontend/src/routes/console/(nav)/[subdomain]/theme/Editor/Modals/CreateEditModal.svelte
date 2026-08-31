<script lang="ts">
	import {
		Button,
		ButtonGroup,
		Caption,
		FormControl,
		Loader,
		Modal,
		SplitControl,
		Text,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { checkFilename, createFile, updateFile } from '../../themeActions';
	import type { ThemeFile, ThemeFolder } from '../../../../../lib/types';
	import { onMount } from 'svelte';
	import {
		addThemeFileToStore,
		selectedThemeFileIdStore,
		updateThemeFileStore
	} from '../../themeStore';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		open?: boolean;
		file: { id: number | null; name: string; folder: ThemeFolder };
	}

	let { open = $bindable(false), file }: Props = $props();

	let isCreate = $derived(file.id === null);

	let fileName = $state('');

	let loaderState: 'none' | 'loading' | 'success' | 'error' = $state('none');
	let inputState: 'default' | 'success' | 'error' = $state('default');
	let error: string | null = $state(null);

	function handleUpdate() {
		const toastId = toast.loading(i18n.t('console.theme.updatingFileName'));
		open = false;

		updateFile(file.id!, {
			name: fileName
		})
			.then(() => {
				toast.success(i18n.t('console.theme.fileNameUpdated'), { id: toastId });
				updateThemeFileStore(file.id!, { name: fileName }, true);
			})
			.catch(() => {
				toast.error(i18n.t('console.theme.failedToUpdateFileName'), { id: toastId });
			});
	}

	function handleCreate() {
		const toastId = toast.loading(i18n.t('console.theme.creatingFile'));
		open = false;

		createFile(file.folder, fileName)
			.then((res) => {
				toast.success(i18n.t('console.theme.fileCreated'), { id: toastId });
				addThemeFileToStore(res);
				selectedThemeFileIdStore.set(res.id);
			})
			.catch(() => {
				toast.error(i18n.t('console.theme.failedToCreateFile'), { id: toastId });
			});
	}

	let timeout: null | ReturnType<typeof setTimeout> = null;
	let abortController: AbortController | null = null;

	function validateFolder(name: string): { state: boolean; error: string | null } {
		if (file.folder === 'lang' && !name.endsWith('.yaml')) {
			return {
				state: false,
				error: 'File name in the lang folder must end with .yaml'
			};
		}

		if (file.folder === 'styles' && !name.endsWith('.scss')) {
			return {
				state: false,
				error: 'File name in the styles folder must end with .scss'
			};
		}

		if (file.folder === 'templates' && !name.endsWith('.twig')) {
			return {
				state: false,
				error: 'File name in the templates folder must end with .twig'
			};
		}

		return {
			state: true,
			error: null
		};
	}

	function handleInput(e: any) {
		const value = e.target.value;

		if (timeout) {
			clearTimeout(timeout);
		}
		if (abortController) {
			abortController.abort();
		}

		if (value.trim() === '') {
			loaderState = 'none';
			inputState = 'error';
			error = i18n.t('console.theme.validation.nameEmpty');
			return;
		}

		if (value.trim() === file.name) {
			loaderState = 'none';
			inputState = 'default';
			error = null;
			return;
		}

		const { state, error: err } = validateFolder(value);

		if (!state) {
			loaderState = 'none';
			inputState = 'error';
			error = err;
			return;
		}

		timeout = setTimeout(() => {
			loaderState = 'loading';
			inputState = 'default';
			error = null;

			abortController = new AbortController();

			checkFilename(value, file.folder, abortController.signal)
				.then((res) => {
					if (res.available) {
						loaderState = 'success';
						inputState = 'success';
					} else {
						loaderState = 'error';
						inputState = 'error';
						error = i18n.t('console.theme.validation.nameExists');
					}
				})
				.catch((e) => {
					if (abortController?.signal.aborted) {
						return;
					}

					loaderState = 'error';
					inputState = 'error';
					error = e.error;
				});
		}, 250);
	}

	onMount(() => {
		fileName = file.name;
	});
</script>

<Modal bind:show={open} title={isCreate ? 'Create new file' : 'Update file name'}>
	<SplitControl label={i18n.t('console.theme.folder')}>
		<Text light>/{file.folder || ''}</Text>
	</SplitControl>

	<SplitControl label={i18n.t('console.theme.fileName')}>
		<FormControl>
			<TextInput block autofocus on:input={handleInput} bind:value={fileName} state={inputState}>
				{#snippet end()}
					<Loader state={loaderState} size="small" duration={10000} />
				{/snippet}
			</TextInput>
			{#if error}
				<Validation state="error">{error}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (open = false)}
				>{i18n.t('console.common.cancel')}</Button
			>

			<Button
				color="accent"
				on:click={() => (isCreate ? handleCreate() : handleUpdate())}
				disabled={inputState !== 'success'}
			>
				{isCreate ? 'Create' : 'Update'}
			</Button>
		</ButtonGroup>
	{/snippet}
</Modal>

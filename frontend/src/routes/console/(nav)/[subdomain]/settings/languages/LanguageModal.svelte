<script lang="ts">
	import {
		Button,
		ButtonGroup,
		FormControl,
		InputGroup,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import type { Language } from '../../../../lib/types';
	import { createLanguage, updateLanguage } from './languageActions';
	import {
		languageStoreAdd,
		languageStoreUpdate,
		languagesStore
	} from '../../../../lib/stores/languagesStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		language?: Language | null;
		show?: boolean;
	}

	let { language = null, show = $bindable(false) }: Props = $props();

	const isCreating = language === null;

	let name = $state(language ? language.name : '');
	let code = $state(language ? language.code : '');
	let direction: 'ltr' | 'rtl' = $state(language ? language.direction : 'ltr');

	let nameError: null | string = $state(null);
	let codeError: null | string = $state(null);

	function handleClick() {
		nameError = null;
		codeError = null;

		if (!name) {
			nameError = i18n.t('console.settings.languages.nameRequired');
			return;
		}

		if (!code) {
			codeError = i18n.t('console.settings.languages.codeRequired');
			return;
		}

		if (isCreating) {
			if ($languagesStore.find((l) => l.code === code)) {
				codeError = i18n.t('console.settings.languages.codeInUse');
				return;
			}

			show = false;

			const toastId = toast.loading(i18n.t('console.settings.languages.creating'));

			createLanguage(name, code, direction)
				.then((res) => {
					toast.success(i18n.t('console.settings.languages.created'), { id: toastId });
					languageStoreAdd(res);
				})
				.catch((err) => {
					toast.error(err.message, { id: toastId });
				});
		} else {
			if ($languagesStore.find((l) => l.code === code && l.id !== language!.id)) {
				codeError = i18n.t('console.settings.languages.codeInUse');
				return;
			}

			show = false;

			const toastId = toast.loading(i18n.t('console.settings.languages.updating'));

			updateLanguage(language!.id, name, code, direction)
				.then((res) => {
					toast.success(i18n.t('console.settings.languages.updated'), { id: toastId });
					languageStoreUpdate(res);
					show = false;
				})
				.catch((err) => {
					toast.error(err.message, { id: toastId });
				});
		}
	}

	let isButtonDisabled = $derived(
		!(
			isCreating ||
			name !== language!.name ||
			code !== language!.code ||
			direction !== language!.direction
		)
	);
</script>

<Modal
	title={isCreating
		? i18n.t('console.settings.languages.addTitle')
		: i18n.t('console.settings.languages.editLanguage')}
	bind:show
>
	<SplitControl
		label={i18n.t('console.common.name')}
		caption={i18n.t('console.settings.languages.nameCaption')}
	>
		<FormControl>
			<TextInput
				bind:value={name}
				placeholder={i18n.t('console.settings.languages.namePlaceholder')}
				block
				state={nameError ? 'error' : undefined}
				autofocus
			/>

			{#if nameError}
				<Validation state="error">
					{nameError}
				</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl
		label={i18n.t('console.settings.languages.code')}
		caption={i18n.t('console.settings.languages.codeCaption')}
	>
		<FormControl>
			<TextInput
				bind:value={code}
				placeholder="en-US"
				block
				state={codeError ? 'error' : undefined}
			/>

			{#if codeError}
				<Validation state="error">
					{codeError}
				</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl label={i18n.t('console.settings.languages.direction')}>
		<InputGroup>
			<Radio name="direction" value="ltr" bind:group={direction}>
				{i18n.t('console.settings.languages.ltr')}
			</Radio>
			<Radio name="direction" value="rtl" bind:group={direction}>
				{i18n.t('console.settings.languages.rtl')}
			</Radio>
		</InputGroup>
	</SplitControl>

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}>
				{i18n.t('console.common.cancel')}
			</Button>

			<Button on:click={handleClick} disabled={isButtonDisabled}>
				{isCreating ? i18n.t('console.common.add') : i18n.t('console.common.save')}
			</Button>
		</ButtonGroup>
	{/snippet}
</Modal>

<script lang="ts">
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
	import IconTrash from '@hyvor/icons/IconTrash';

	import type { Language } from '../../../../lib/types';
	import {
		IconButton,
		Modal,
		TableRow,
		Tag,
		Tooltip,
		confirm,
		toast
	} from '@hyvor/design/components';
	import LanguageModal from './LanguageModal.svelte';
	import { deleteLanguage } from './languageActions';
	import { languageStoreRemove } from '../../../../lib/stores/languagesStore';
	import DeleteConfirm from './DeleteConfirm.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		language: Language;
	}

	let { language }: Props = $props();

	let isEditing = $state(false);

	async function handleDelete() {
		if (
			await confirm({
				title: i18n.t('console.settings.languages.deleteTitle'),
				content: DeleteConfirm,
				contentProps: {
					language
				},
				confirmText: i18n.t('console.settings.languages.deleteConfirm'),
				danger: true
			})
		) {
			const toastId = toast.loading(i18n.t('console.settings.languages.deleting'));

			deleteLanguage(language.id)
				.then(() => {
					toast.success(i18n.t('console.settings.languages.deleted'), { id: toastId });
					languageStoreRemove(language.id);
				})
				.catch((err) => {
					toast.error(err.message, { id: toastId });
				});
		}
	}
</script>

<TableRow>
	<div>
		{language.name}
		{#if language.is_primary}
			<Tag size="x-small" color="accent">
				{i18n.t('console.settings.languages.primary').toUpperCase()}
			</Tag>
		{/if}
	</div>
	<div>{language.code}</div>
	<div>{language.direction.toUpperCase()}</div>
	<div>
		<Tooltip text={i18n.t('console.settings.languages.editLanguage')}>
			<IconButton
				variant="fill-light"
				color="gray"
				size="small"
				on:click={() => (isEditing = true)}
			>
				<IconPencilFill size={12} />
			</IconButton>
		</Tooltip>
		<Tooltip
			text={language.is_primary
				? i18n.t('console.settings.languages.cannotDeletePrimary')
				: i18n.t('console.settings.languages.deleteLanguage')}
		>
			<IconButton
				variant="fill-light"
				color="red"
				size="small"
				on:click={handleDelete}
				disabled={language.is_primary}
			>
				<IconTrash size={12} />
			</IconButton>
		</Tooltip>
	</div>
</TableRow>

<LanguageModal {language} bind:show={isEditing} />

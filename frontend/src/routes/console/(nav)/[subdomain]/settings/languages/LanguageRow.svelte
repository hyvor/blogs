<script lang="ts">
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
	import IconTrash from '@hyvor/icons/IconTrash';

	import type { Language } from '../../../../lib/types';
	import { IconButton, TableRow, Tag, Tooltip } from '@hyvor/design/components';
	import LanguageModal from './LanguageModal.svelte';
	import DeleteConfirmModal from './DeleteConfirmModal.svelte';
	import { getI18n } from '../../../../lib/i18n';
	import { cant } from '../../../../lib/scope.svelte';

	const i18n = getI18n();

	interface Props {
		language: Language;
	}

	let { language }: Props = $props();

	let isEditing = $state(false);

	let isDeleting = $state(false);
</script>

<TableRow>
	<div>
		{language.name}
		{#if language.is_primary}
			<Tag size="x-small" color="accent"
				>{i18n.t('console.settings.languages.primary').toUpperCase()}</Tag
			>
		{/if}
	</div>
	<div>{language.code}</div>
	<div>{language.direction.toUpperCase()}</div>
	<div>
		<Tooltip text={i18n.t('console.settings.languages.editLanguage')}>
			<IconButton
				color="input"
				variant="fill"
				size="small"
				on:click={() => (isEditing = true)}
				disabled={cant('languages.write')}
			>
				<IconPencilFill size={12} />
			</IconButton>
		</Tooltip>
		<Tooltip text={language.is_primary ? 'Cannot delete primary language' : 'Delete language'}>
			<IconButton
				variant="fill-light"
				color="red"
				size="small"
				on:click={() => (isDeleting = true)}
				disabled={language.is_primary || cant('languages.write')}
			>
				<IconTrash size={12} />
			</IconButton>
		</Tooltip>
	</div>
</TableRow>

<LanguageModal {language} bind:show={isEditing} />
<DeleteConfirmModal {language} bind:show={isDeleting} />

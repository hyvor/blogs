<script lang="ts">
	import { Button, Modal, toast } from '@hyvor/design/components';
	import type { Language } from '../../../../lib/types';
	import { getI18n } from '../../../../lib/i18n';
	import { deleteLanguage } from './languageActions';
	import { languageStoreRemove } from '../../../../lib/stores/languagesStore';

	const i18n = getI18n();
	const T = i18n.T;

	interface Props {
		language: Language;
		show?: boolean;
	}

	let { language, show = $bindable(false) }: Props = $props();

	function handleDelete() {
		show = false;
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
</script>

<Modal title={i18n.t('console.settings.languages.deleteLanguage')} bind:show size="small">
	<T
		key="console.settings.languages.deleteContent"
		params={{ strong: { element: 'strong' }, language: language.name, code: language.code }}
	/>

	<ul>
		<li>
			<T
				key="console.settings.languages.deleteScope.blog"
				params={{ strong: { element: 'strong' } }}
			/>
		</li>
		<li>
			<strong>{i18n.t('console.nav.posts')}</strong>
		</li>
		<li>
			<strong>{i18n.t('console.settings.nav.users')}</strong>
		</li>
		<li>
			<strong>{i18n.t('console.settings.nav.tags')}</strong>
		</li>
		<li>
			<strong>{i18n.t('console.settings.languages.deleteScope.navigations')}</strong>
		</li>
	</ul>

	{#snippet footer()}
		<Button variant="invisible" on:click={() => (show = false)}
			>{i18n.t('console.common.cancel')}</Button
		>

		<Button on:click={handleDelete} color="red">{i18n.t('console.common.yesDelete')}</Button>
	{/snippet}
</Modal>

<script lang="ts">
	import {
		IconButton,
		Link,
		TableRow,
		Tooltip,
		confirm,
		toast,
		Tag
	} from '@hyvor/design/components';
	import type { Redirect } from '../../../../lib/types';
	import { primaryLanguageStore } from '../../../../lib/stores/languagesStore';
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { deleteRedirect } from './redirectActions';
	import { createEventDispatcher } from 'svelte';
	import RedirectsModal from './RedirectsModal.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		redirect: Redirect;
	}

	let { redirect }: Props = $props();

	let isEditing = $state(false);

	const dispatch = createEventDispatcher();

	async function handleDelete() {
		if (
			await confirm({
				title: i18n.t('console.settings.redirects.deleteRedirect'),
				content: i18n.t('console.settings.redirects.deleteContent'),
				confirmText: i18n.t('console.common.yesDelete'),
				danger: true
			})
		) {
			const toastId = toast.loading(i18n.t('console.settings.redirects.deleting'));

			deleteRedirect(redirect.id)
				.then(() => {
					toast.success(i18n.t('console.settings.redirects.deleted'), { id: toastId });
					dispatch('delete', redirect.id);
				})
				.catch((e) => {
					toast.error(e.message, { id: toastId });
				});
		}
	}
</script>

<TableRow>
	<div>
		{redirect.path}
		{#if redirect.dynamic}
			<Tag size="small" color="orange">{i18n.t('console.settings.redirects.dynamic')}</Tag>
		{/if}
	</div>
	<div>
		<Link href={redirect.to} target="_blank">{redirect.to}</Link>
	</div>
	<div>
		{#if redirect.type === 'permanent'}
			{i18n.t('console.settings.redirects.permanent')}
		{:else}
			{i18n.t('console.settings.redirects.temporary')}
		{/if}
	</div>
	<div>
		<Tooltip text={i18n.t('console.settings.redirects.editRedirect')}>
			<IconButton
				variant="fill-light"
				color="gray"
				size="small"
				on:click={() => (isEditing = true)}
			>
				<IconPencilFill size={12} />
			</IconButton>
		</Tooltip>
		<Tooltip text={i18n.t('console.settings.redirects.deleteRedirect')}>
			<IconButton variant="fill-light" color="red" size="small" on:click={handleDelete}>
				<IconTrash size={12} />
			</IconButton>
		</Tooltip>
	</div>
</TableRow>

{#if isEditing}
	<RedirectsModal bind:show={isEditing} on:update {redirect} />
{/if}

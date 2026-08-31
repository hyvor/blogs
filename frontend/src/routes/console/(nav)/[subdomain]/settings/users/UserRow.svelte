<script lang="ts">
	import {
		IconButton,
		Link,
		TableRow,
		Tag,
		Tooltip,
		confirm,
		toast
	} from '@hyvor/design/components';
	import type { User } from '../../../../lib/types';
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { createEventDispatcher } from 'svelte';
	import { deleteUser } from './userActions';
	import UpdateUser from './Update/UpdateUser.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		user: User;
	}

	let { user }: Props = $props();

	let variant = $derived(user.variants[0]);

	let isEditing = $state(false);

	const dispatch = createEventDispatcher<{ delete: number; update: User }>();

	async function handleDelete() {
		if (
			await confirm({
				title: i18n.t('console.settings.users.removeTitle'),
				content: i18n.t('console.settings.users.removeContent'),
				confirmText: i18n.t('console.common.yesDelete'),
				danger: true
			})
		) {
			const toastId = toast.loading(i18n.t('console.settings.users.deleting'));

			deleteUser(user.id)
				.then(() => {
					toast.success(i18n.t('console.settings.users.deleted'), { id: toastId });
					dispatch('delete', user.id);
				})
				.catch((e) => {
					toast.error(e.message, { id: toastId });
				});
		}
	}
</script>

<TableRow>
	<div>
		<div>{variant?.name}</div>
		<div class="slug">
			<Link href={variant?.url || ''} target="_blank">
				{user.slug}
			</Link>
		</div>
	</div>
	<div>
		{#if user.hyvor_user_id}
			{#if user.status === 'active'}
				<Tag size="x-small" color="green"
					>{i18n.t('console.settings.users.status.active').toUpperCase()}</Tag
				>
			{:else if user.status === 'invited'}
				<Tag size="x-small" color="blue"
					>{i18n.t('console.tools.jobStatus.pending').toUpperCase()}</Tag
				>
			{:else if user.status === 'blocked'}
				<Tag size="x-small" color="red"
					>{i18n.t('console.settings.users.status.blocked').toUpperCase()}</Tag
				>
			{/if}
		{:else}
			<Tag size="x-small" color="orange"
				>{i18n.t('console.settings.users.status.guest').toUpperCase()}</Tag
			>
		{/if}
	</div>
	<div>
		{#if user.hyvor_user_id}
			<Tag size="x-small">{user.role.toUpperCase()}</Tag>
		{/if}
	</div>
	<div>{user.posts_count}</div>
	<div>
		<Tooltip text={i18n.t('console.settings.users.editUser')}>
			<IconButton color="input" size="small" on:click={() => (isEditing = true)}>
				<IconPencilFill size={12} />
			</IconButton>
		</Tooltip>

		{#if user.role !== 'owner'}
			<Tooltip text={i18n.t('console.settings.users.deleteUser')}>
				<IconButton color="input" size="small" on:click={handleDelete}>
					<IconTrash size={12} />
				</IconButton>
			</Tooltip>
		{/if}
	</div>
</TableRow>

{#if isEditing}
	<UpdateUser {user} on:update on:variantCreate bind:show={isEditing} />
{/if}

<style>
	.slug {
		margin-top: 2px;
		font-size: 14px;
	}
</style>

<script lang="ts">
	import {
		Button,
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
	import { deleteUser, resendInvitation } from './userActions';
	import UpdateUser from './Update/UpdateUser.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	const ROLE_KEYS = {
		owner: 'console.settings.users.roles.owner',
		admin: 'console.settings.users.roles.admin',
		editor: 'console.settings.users.roles.editor',
		writer: 'console.settings.users.roles.writer',
		contributor: 'console.settings.users.roles.contributor'
	} as const;

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
				confirmText: i18n.t('console.settings.users.removeConfirm'),
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

	async function handelResend() {
		if (
			await confirm({
				title: i18n.t('console.settings.users.resendTitle'),
				content: i18n.t('console.settings.users.resendContent'),
				confirmText: i18n.t('console.settings.users.resendConfirm')
			})
		) {
			const toastId = toast.loading(i18n.t('console.settings.users.resending'));

			resendInvitation(user.id)
				.then(() => {
					toast.success(i18n.t('console.settings.users.invitationSent'), { id: toastId });
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
				<Tag size="x-small" color="green">
					{i18n.t('console.settings.users.status.active').toUpperCase()}
				</Tag>
			{:else if user.status === 'invited'}
				<Tag size="x-small" color="blue">
					{i18n.t('console.settings.users.status.pending').toUpperCase()}
				</Tag>
				<Tooltip text={i18n.t('console.settings.users.resendTooltip')}>
					<Button size="x-small" on:click={handelResend} style="margin-top:8px;">
						{i18n.t('console.settings.users.resend')}
					</Button>
				</Tooltip>
			{:else if user.status === 'blocked'}
				<Tag size="x-small" color="red">
					{i18n.t('console.settings.users.status.blocked').toUpperCase()}
				</Tag>
			{/if}
		{:else}
			<Tag size="x-small" color="orange">
				{i18n.t('console.settings.users.status.guest').toUpperCase()}
			</Tag>
		{/if}
	</div>
	<div>
		{#if user.hyvor_user_id}
			<Tag size="x-small">{i18n.t(ROLE_KEYS[user.role]).toUpperCase()}</Tag>
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

	<!-- {
        isResendingEmail && <PopupConfirm
            title="Resend Invitation"
            text="Please confirm to re-send an invitation to this user."
            name="Resend"
            onClick={handleResend}
            onCancel={() => setIsResendingEmail(false)}
        />
    }

    {
        isUpdating && <UpdateUserPopup user={user} onClose={() => setIsUpdating(false)} />
    }
 -->
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

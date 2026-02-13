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
				title: 'Remove user',
				content:
					'Are you sure you want to remove this user? This user will be removed as an author from all posts, and will no longer be able to access the blog. Posts created by this user will not be deleted.',
				confirmText: 'Yes, delete',
				danger: true
			})
		) {
			const toastId = toast.loading('Deleting user...');

			deleteUser(user.id)
				.then(() => {
					toast.success('User deleted.', { id: toastId });
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
				title: 'Resend Invitation',
				content: 'Please confirm to re-send an invitation to this user.',
				confirmText: 'Yes, resend'
			})
		) {
			const toastId = toast.loading('Resending invitation...');

			resendInvitation(user.id)
				.then(() => {
					toast.success('Invitation sent.', { id: toastId });
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
				<Tag size="x-small" color="green">ACTIVE</Tag>
			{:else if user.status === 'invited'}
				<Tag size="x-small" color="blue">PENDING</Tag>
				<Tooltip text="Resend invitation email">
					<Button size="x-small" on:click={handelResend} style="margin-top:8px;">Resend</Button>
				</Tooltip>
			{:else if user.status === 'blocked'}
				<Tag size="x-small" color="red">BLOCKED</Tag>
			{/if}
		{:else}
			<Tag size="x-small" color="orange">GUEST</Tag>
		{/if}
	</div>
	<div>
		{#if user.hyvor_user_id}
			<Tag size="x-small">{user.role.toUpperCase()}</Tag>
		{/if}
	</div>
	<div>{user.posts_count}</div>
	<div>
		<Tooltip text="Edit user data">
			<IconButton color="input" size="small" on:click={() => (isEditing = true)}>
				<IconPencilFill size={12} />
			</IconButton>
		</Tooltip>

		{#if user.role !== 'owner'}
			<Tooltip text="Delete user">
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

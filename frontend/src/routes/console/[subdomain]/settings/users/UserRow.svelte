<script lang="ts">
	import { IconButton, Link, TableRow, Tag, Tooltip, confirm, toast } from "@hyvor/design/components";
    import type { User } from "../../../lib/types";
	import { IconPencilFill, IconTrash } from "@hyvor/icons";
	import { createEventDispatcher } from "svelte";
	import { deleteUser } from "./userActions";

    export let user: User;

    $: variant = user.variants[0];

    let isEditing = false;

    const dispatch = createEventDispatcher();

    async function handleDelete() {
        if (await confirm({
            title: 'Remove user',
            content: 'Are you sure you want to remove this user? This user will be removed as an author from all posts, and will no longer be able to access the blog. Posts created by this user will not be deleted.',
            confirmText: 'Yes, delete',
            danger: true,
        })) {

            const toastId = toast.loading('Deleting user...');

            deleteUser(user.id)
                .then(() => {
                    toast.success('User deleted.', {id: toastId});
                    dispatch('delete', user.id)
                })
                .catch(e => {
                    toast.error(e.message, {id: toastId});
                });

        }
    }

</script>

<TableRow>
    <div>
        <div>{ variant?.name }</div>
    </div>
    <div>
        <Link href={variant?.url || ''} target="_blank">
            {user.slug}
        </Link>
    </div>
    <div>
        {#if user.hyvor_user_id}
            {#if user.status === 'active'}
                <Tag size="small" color="green">ACTIVE</Tag>
            {:else if user.status === 'invited'}
                <Tag size="small" color="blue">PENDING</Tag>
                <!-- TODO: Add Resent -->
            {:else if user.status === 'blocked'}
                <Tag size="small" color="red">BLOCKED</Tag>
            {/if}
        {:else}
            <Tag size="small" color="orange">GUEST</Tag>
        {/if}
    </div>
    <div>
        {#if user.hyvor_user_id}
            <Tag size="small">{user.role}</Tag>
        {/if}
    </div>
    <div>{ user.posts_count }</div>
    <div>

        <Tooltip text="Edit user data">
            <IconButton
                variant="fill-light" 
                color="gray"
                size="small"
                on:click={() => isEditing = true}
            >
                <IconPencilFill size={12} />
            </IconButton>
        </Tooltip>

        {#if user.role !== 'owner'}
            <Tooltip text="Delete user">
                <IconButton 
                    variant="fill-light" 
                    color="red" 
                    size="small"
                    on:click={handleDelete}
                >
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

    {
        isDeleting &&
        <PopupConfirm
            title="Remove User"
            text={
                <div>
                    <p>
                        Please confirm to remove this user from the blog.
                    </p>
                    <ul>
                        <li>The user will be removed as an author from all posts.</li>
                        <li>Posts created by this user will not be deleted.</li>
                    </ul>
                </div>
            }
            name="Remove"
            buttonClass="danger"
            onClick={handleDelete}
            onCancel={() => setIsDeleting(false)}
        />
    }
 -->
</TableRow>
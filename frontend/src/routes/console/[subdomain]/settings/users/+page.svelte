<script lang="ts">
	import type { User, UserVariant } from './../../../lib/types';
	import { IconPlus } from '@hyvor/icons';
	import { Button, Loader, IconMessage, Table, TableRow, LoadButton, toast } from "@hyvor/design/components";
	import SettingsTop from "../@components/SettingsTop.svelte";
	import UserRow from "./UserRow.svelte";
	import { getUsers } from "./userActions";
	import { onMount } from "svelte";
	import Slug from "../../posts/[postId]/Sidebar/Settings/Slug.svelte";
	import DisabledOnTemp from "../../Temp/DisabledOnTemp.svelte";

    let isLoading = true;
    let isCreating = false;
    let hasMore = false;
    let isLoadingMore = false;

    let users: User[] = [];

    const limit = 20;

    function loadUsers(more = false) {
        more ? isLoadingMore = true : isLoading = true;

        getUsers({
            limit,
            offset: more ? users.length : 0,
        })
            .then(res => {
                users = more ? [...users, ...res] : res;
                hasMore = res.length === limit;
            })
            .catch(e => {
                if (!more) users = [];
                toast.error(e.message || "Failed to load users.");
            })
            .finally(() => {
                isLoading = false;
                isLoadingMore = false;
            })
    }

    function handleCreate(e: CustomEvent<User>) {
        users = [e.detail, ...users];
    }

    function handleDelete(e: CustomEvent<number>) {
        users = users.filter(t => t.id !== e.detail);
    }

    function handleCreateVariant(e: CustomEvent<{id: number, variant: UserVariant}>) {
        users = users.map(t => {
            const newTag = t.id === e.detail.id ? 
                {...t, variants: [...t.variants, e.detail.variant]} : 
                t;
            return newTag;
        });
    }

    function handleUpdate(e: CustomEvent<User>) {
        users = users.map(t => t.id === e.detail.id ? e.detail : t);
    }

    onMount(loadUsers);

</script>


<DisabledOnTemp>

    <SettingsTop>

        <Button on:click={() => isCreating = true}>
            Add User <IconPlus slot="end" />
        </Button>

    </SettingsTop>

    <div class="table">

        {#if isLoading}
            <Loader full />
        {:else}

            {#if users.length === 0}
                <IconMessage empty message="No users found" />
            {:else}

                <Table columns="1fr 1fr 1fr 1fr 1fr 70px">
                    
                    <TableRow head>
                        <div>Name</div>
                        <div>Slug</div>
                        <div>Status</div>
                        <div>Role</div>
                        <div>Posts</div>
                        <div />
                    </TableRow>

                    {#each users as user}
                        <UserRow
                            {user}
                            on:delete={handleDelete}
                            on:update={handleUpdate}
                            on:variantCreate={handleCreateVariant}
                        />
                    {/each}

                    <LoadButton
                        text="Load More"
                        show={hasMore}
                        on:click={() => loadUsers(true)}
                        loading={isLoadingMore}
                    />

                </Table>

            {/if}

        {/if}

    </div>

</DisabledOnTemp>

<style>
    .table {
        flex: 1;
        padding: 15px 30px;
    }
</style>
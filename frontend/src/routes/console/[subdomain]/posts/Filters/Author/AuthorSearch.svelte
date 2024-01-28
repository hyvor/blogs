<script lang="ts">
	import { ActionListItem, Avatar, IconMessage, Loader, Text, TextInput } from "@hyvor/design/components";
	import type { User } from "../../../../lib/types";
	import { createEventDispatcher, onMount } from "svelte";
	import { getUsers, searchUsers } from "../../../settings/users/userActions";
	import { postListFiltersStore } from "../../postListStore";

    let isLoading = true;
    let users : User[] = [];
    let search = '';

    let err = false;

    function loadUsers() {
        isLoading = true;
        users = [];

        const promise = search.trim() ? 
            searchUsers({ search }) :
            getUsers();

        promise
            .then(res => {
                users = res;
                isLoading = false;
            })
            .catch(_ => err = true)
            .finally(() => isLoading = false);

    }

    const dispatch = createEventDispatcher<{
        select: User
    }>();

    function handleSelect(user: User) {
        dispatch('select', user);
    }

    let timeout : null | ReturnType<typeof setTimeout> = null;

    function handleSearchInput() {
        if (timeout) clearTimeout(timeout);

        timeout = setTimeout(() => {
            loadUsers();
        }, 500);
    }

    onMount(loadUsers);

</script>


<TextInput 
    block
    placeholder="Search author..."
    autofocus
    bind:value={search}
    on:input={handleSearchInput}
/>

<div class="results">

    {#if isLoading}
        <Loader block padding={35} size="small" />
    {:else if err}
        <IconMessage error padding={35} />
    {:else if users.length === 0}
        <IconMessage 
            empty 
            padding={35} 
            message="No users found" 
            iconSize={40}
        />
    {:else}
        {#each users as user (user.id)}
            <ActionListItem
                on:click={() => handleSelect(user)}
                selected={$postListFiltersStore.author?.id === user.id}
            >
                <Avatar 
                    src={user.picture_url} 
                    alt={user.variants[0]?.name || 'Unnamed'}
                    size={20}
                    slot="start"
                />
                <span class="text">
                    {user.variants[0]?.name || 'Unnamed'}
                </span>
                <Text light slot="end" small>
                    {user.posts_count} post{user.posts_count === 1 ? '' : 's'}
                </Text>
            </ActionListItem>
        {/each}
    {/if}

</div>

<style>
    .results {
        margin-top: 10px;
        max-height: 350px;
        overflow: auto;
    }
</style>
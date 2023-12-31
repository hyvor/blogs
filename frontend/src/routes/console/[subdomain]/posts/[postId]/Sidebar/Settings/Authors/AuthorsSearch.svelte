<script lang="ts">
	import AuthorRow from './AuthorRow.svelte';
	import { Loader, Text, TextInput } from "@hyvor/design/components";
	import { createEventDispatcher, onMount } from "svelte";
	import type { User } from "../../../../../../lib/types";
	import { getUsers, searchUsers } from "../../../../../../lib/actions/userActions";

    let isLoading = true;
    let users : User[] = [];
    let searchedUsers : User[] = [];
    let search = '';

    $: availableUsers = search.trim() !== '' ? searchedUsers : users;

    let searchTimeout : null | ReturnType<typeof setTimeout> = null;

    function handleInput(event: Event) {
        search = (event.target as HTMLInputElement).value;
        isLoading = true;

        if (search.trim() === '') {
            isLoading = false;
            searchedUsers = [];
            return;
        }

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        setTimeout(() => {
            searchUsers(search)
                .then(res => {
                    isLoading = false;
                    searchedUsers = res;
                });
        }, 250);

    }

    const dispatch = createEventDispatcher();

    function handleSelect(user: User) {
        dispatch('select', user);
    }

    onMount(() => {
        getUsers()
            .then(res => {
                isLoading = false;
                users = res;
            })
    });
</script>

<div class="search">

    <div class="input">
        <TextInput
            bind:value={search}
            placeholder="Search authors"
            size="small"
            block
            autofocus
            on:input={handleInput}
        />
    </div>

    <div class="results">
        {#if isLoading}
            <Loader block padding={30} size="small" />
        {:else}

            {#if availableUsers.length}

                {#each availableUsers as user (user.id)}
                    <AuthorRow {user} on:click={() => handleSelect(user)} />
                {/each}

            {:else}
                <div style="padding:30px;text-align:center">
                    <Text small light>No authors</Text>
                </div>
            {/if}

        {/if}
    </div>

</div>

<style>

    .input :global(.input-wrap) {
        height: 26px!important;
    }

    .results {
        margin-top: 10px;
        max-height: 200px;
        overflow: auto;
    }

</style>
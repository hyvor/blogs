<script lang="ts">
	import { Avatar, Loader, Text, TextInput } from "@hyvor/design/components";
	import { onMount } from "svelte";
	import type { User } from "../../../../../../../lib/types";
	import consoleApi from "../../../../../../../lib/consoleApi";

    let isLoading = true;
    let users : User[] = [];

    onMount(() => {

        consoleApi.get<User[]>({
            endpoint: 'users',
        }).then(res => {
            isLoading = false;
            users = [];// res;
        })

    });
</script>

<div class="search">

    <div class="input">
        <TextInput
            placeholder="Search authors"
            size="small"
            block
            autofocus
        />
    </div>

    <div class="results">
        {#if isLoading}
            <Loader block padding={30} size="small" />
        {:else}

            {#if users.length}

                {#each users as user}
                    <div class="user-row">
                        <div class="left">
                            <Avatar size={24} src={user.picture_url} alt={user.name} />
                        </div>
                    </div>
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
        height: 26px;
    }

</style>
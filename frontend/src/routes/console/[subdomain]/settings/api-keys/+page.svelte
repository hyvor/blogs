<script lang="ts">
	import ApiKeyRow from './ApiKeyRow.svelte';
	import { Button, IconMessage, Loader, Table, TableRow, toast } from "@hyvor/design/components";
    import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconPlus } from "@hyvor/icons";
	import CreateApiKeyModal from "./CreateApiKeyModal.svelte";
	import type { ApiKey } from "../../../lib/types";
	import { onMount } from "svelte";
	import { getApiKeys } from "./apiKeysActions";

    let isCreating = false;

    let isLoading = true;
    let apiKeys : ApiKey[] = [];

    function handleCreate(e: CustomEvent<ApiKey>) {
        apiKeys = [e.detail, ...apiKeys];
    }

    function handleDeleteEvent(e: CustomEvent<number>) {
        apiKeys = apiKeys.filter(apiKey => apiKey.id !== e.detail);
    }

    function handleUpdateEvent(e: CustomEvent<ApiKey>) {
        console.log(e.detail)
        apiKeys = apiKeys.map(apiKey => {
            if (apiKey.id === e.detail.id) {
                return e.detail;
            }
            return apiKey;
        });
    }

    onMount(() => {
        getApiKeys()
            .then(res => {
                apiKeys = res;
            })
            .catch(err => {
                toast.error(err.message);
            })
            .finally(() => {
                isLoading = false;
            })
    });

</script>

<SettingsTop>

    <Button on:click={() => isCreating = true}>
        Create API Key <IconPlus slot="end" />
    </Button>

</SettingsTop>

<div class="api-keys">

    {#if isLoading}
        <Loader full />
    {:else}
        {#if apiKeys.length === 0}
            <IconMessage empty message="No API Keys Found" />
        {:else}

            <Table columns="1fr 1fr 140px">

                <TableRow head>
                    <div>Name</div>
                    <div>API</div>
                    <div/>
                </TableRow>

                {#each apiKeys as apiKey (apiKey.id)}
                    <ApiKeyRow 
                        {apiKey}
                        on:delete={handleDeleteEvent}
                        on:update={handleUpdateEvent}
                    />
                {/each}
            
            </Table>
        {/if}
    {/if}

</div>

{#if isCreating}
    <CreateApiKeyModal 
        bind:show={isCreating}
        on:create={handleCreate}
    />
{/if}

<style>
    .api-keys {
        padding: 15px 30px;
        flex: 1;
        overflow: auto;
    }
</style>
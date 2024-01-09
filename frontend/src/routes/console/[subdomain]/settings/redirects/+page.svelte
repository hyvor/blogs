<script lang="ts">
	import { Button, ButtonGroup, FormControl, IconMessage, Loader, Modal, SplitControl, Table, TableRow, TextInput, toast } from "@hyvor/design/components";
    import RedirectsModal from './RedirectsModal.svelte';
	import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconPlus } from "@hyvor/icons";
	import type { Redirect } from "../../../lib/types";
	import { getRedirect } from "./redirectActions";
	import { onMount } from "svelte";
	import RedirectRow from "./RedirectRow.svelte";

    let isCreating = false;

    let redirects : Redirect[] = [];
    let isLoading = true;

    function loadRedirect() {
        getRedirect()
            .then(res => {
                redirects = res;
            })
            .catch(err => {
                toast.error(err.message);
            })
            .finally(() => {
                isLoading = false;
            })
        }

    function handleCreate(e: CustomEvent<Redirect>) {
        redirects = [e.detail, ...redirects];
    }

    function handleDelete(e: CustomEvent<number>) {
        redirects = redirects.filter(t => t.id !== e.detail);
    }

    function handleUpdate(e: CustomEvent<Redirect>) {
        redirects = redirects.map(t => t.id === e.detail.id ? e.detail : t);
    }

    onMount(loadRedirect)
</script>

<SettingsTop>
    <Button on:click={() => isCreating = true}>
        Add Redirect <IconPlus slot="end" />
    </Button>
</SettingsTop>

<div class="redirects">
    
    {#if isLoading}
        <Loader full />
    {:else}
        {#if redirects.length === 0}
            <IconMessage empty message="No Redirects configured" />
        {:else}

        <Table columns="1fr 2fr 1fr 70px">
            <TableRow head>
                <div>From</div>
                <div>To</div>
                <div>Type</div>
                <div></div>
            </TableRow>

            {#each redirects as redirect (redirect.id)}
                <RedirectRow 
                    {redirect}
                    on:delete={handleDelete}
                    on:update={handleUpdate}
                />
            {/each}
        </Table>
        {/if}
    {/if}
</div>

{#if isCreating}
    <RedirectsModal
        bind:show={isCreating}
        on:create={handleCreate}
        on:updated={handleUpdate}
    />
{/if}

<style>
     .redirects {
        padding: 15px 30px;
        flex: 1;
        overflow: auto;
    }
</style>
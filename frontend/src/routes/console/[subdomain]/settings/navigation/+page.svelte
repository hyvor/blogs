<script lang="ts">
	import { Button, IconMessage, LoadButton, Loader, Table, TableRow, toast } from "@hyvor/design/components";
	import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconPlus } from "@hyvor/icons";
	import type { Navigation, NavigationVariant } from "../../../lib/types";
	import { onMount } from "svelte";
	import { getNavigations } from "./navigationActions";
	import NavigationRow from "./NavigationRow.svelte";
	import CreateNavigationModal from "./CreateNavigationModal.svelte";

    let isCreating = false;
    
    let navigations : Navigation[] = [];
    let headerNavigations: Navigation[] = [];
    let footerNavigations: Navigation[] = [];
    let isLoading = true;

  
    function loadNavigation() {
        getNavigations()
            .then(res => {
                navigations = res;
                headerNavigations = navigations.filter(t => t.type === 'header');
                footerNavigations = navigations.filter(t => t.type === 'footer');
            })
            .catch(err => {
                toast.error(err.message);
            })
            .finally(() => {
                isLoading = false;
            })
        }

    function handleCreate(e: CustomEvent<Navigation>) {
        navigations = [e.detail, ...navigations];
    }

    function handleDelete(e: CustomEvent<number>) {
        navigations = navigations.filter(t => t.id !== e.detail);
    }

    function handleCreateVariant(e: CustomEvent<{id: number, variant: NavigationVariant}>) {
        navigations = navigations.map(t => {
            const newNavigation = t.id === e.detail.id ? 
                {...t, variants: [...t.variants, e.detail.variant]} : 
                t;
            return newNavigation;
        });
    }

    function handleUpdate(e: CustomEvent<Navigation>) {
        navigations = navigations.map(t => t.id === e.detail.id ? e.detail : t);
        headerNavigations = navigations.filter(t => t.type === 'header');
        footerNavigations = navigations.filter(t => t.type === 'footer');
    }

    onMount(loadNavigation);

</script>


<div class="navigations">

    <SettingsTop>
        <Button on:click={() => isCreating = true}>
            Create Navigation <IconPlus slot="end" />
        </Button>
    </SettingsTop>

    <div class="table">

        {#if isLoading}
            <Loader full /> 
        {:else}
            {#if navigations.length === 0}
                <IconMessage empty message="No navigations found." />
            {:else}
                <div class="nav-title">Header Navigation</div>
                <Table columns="2fr 2fr 70px">

                    <TableRow head>
                        <div>Name</div>
                        <div>URL</div>
                        <div></div>
                    </TableRow>


                    {#each headerNavigations as navigation (navigation.id)}
                        <NavigationRow 
                            {navigation}
                            on:delete={handleDelete}
                            on:variantCreate={handleCreateVariant}
                            on:update={handleUpdate}
                        />
                    {/each}
                </Table>

                <div class="nav-title">Footer Navigation</div>
                <Table columns="2fr 2fr 70px">

                    <TableRow head>
                        <div>Name</div>
                        <div>URL</div>
                        <div></div>
                    </TableRow>


                    {#each footerNavigations as navigation (navigation.id)}
                        <NavigationRow 
                            {navigation}
                            on:delete={handleDelete}
                            on:variantCreate={handleCreateVariant}
                            on:update={handleUpdate}
                        />
                    {/each}
                </Table>
            {/if}

        {/if}

    </div>

</div>

{#if isCreating}
    <CreateNavigationModal 
        bind:show={isCreating}
        on:create={handleCreate}
    />
{/if}

<style>

    .navigations {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: auto;
    }

    .table {
        flex: 1;
        padding: 15px 30px;
    }

    .nav-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
    }

</style>
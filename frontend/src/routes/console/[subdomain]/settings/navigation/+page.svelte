<script lang="ts">
	import { Button, IconMessage, Loader, TabNav, TabNavItem, toast } from "@hyvor/design/components";
	import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconPlus } from "@hyvor/icons";
	import type { Navigation, NavigationVariant } from "../../../lib/types";
	import { onMount } from "svelte";
	import { getNavigations } from "./navigationActions";
	import CreateNavigationModal from "./CreateNavigationModal.svelte";
	import NavTable from "./NavTable.svelte";

    let isCreating = false;
    
    let items : Navigation[] = [];
    let isLoading = true;

  
    function loadNavigation() {
        getNavigations()
            .then(res => {
                items = res;
            })
            .catch(err => {
                toast.error(err.message);
            })
            .finally(() => {
                isLoading = false;
            })
        }

    function handleCreate(e: CustomEvent<Navigation>) {
        items = [e.detail, ...items];
        activeTab = e.detail.type;
    }

    function handleDelete(e: CustomEvent<number>) {
        items = items.filter(t => t.id !== e.detail);
    }
    
    function handleUpdate(e: CustomEvent<Navigation>) {
        items = items.map(t => t.id === e.detail.id ? e.detail : t);
    }

    function handleCreateVariant(e: CustomEvent<{id: number, variant: NavigationVariant}>) {
        items = items.map(t => {
            const newNavigation = t.id === e.detail.id ? 
                {...t, variants: [...t.variants, e.detail.variant]} : 
                t;
            return newNavigation;
        });
    }

    onMount(loadNavigation);

    let activeTab: 'header' | 'footer' = 'header';

</script>


<div class="items">

    <SettingsTop>
        <Button on:click={() => isCreating = true}>
            Create Navigation <IconPlus slot="end" />
        </Button>
    </SettingsTop>

    <div class="table">

        {#if isLoading}
            <Loader full /> 
        {:else}
            {#if items.length === 0}
                <IconMessage empty message="No items found." />
            {:else}
                
                <TabNav bind:active={activeTab}>
                    <TabNavItem name="header">
                        Header
                    </TabNavItem>
                    <TabNavItem name="footer">
                        Footer
                    </TabNavItem>
                </TabNav>

                <NavTable 
                    items={items.filter(t => t.type === activeTab)}
                    on:variantCreate={handleCreateVariant}
                    on:delete={handleDelete}    
                    on:update={handleUpdate}
                />

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

    .items {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: auto;
    }

    .table {
        flex: 1;
        padding: 15px 30px;
    }

</style>
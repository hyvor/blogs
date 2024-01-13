<script lang="ts">
	import { Button, IconMessage, LoadButton, Loader, Table, TableRow, toast } from "@hyvor/design/components";
	import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconGripVertical, IconPlus } from "@hyvor/icons";
	import type { Navigation, NavigationVariant } from "../../../lib/types";
	import { onMount } from "svelte";
	import { getNavigations, saveSort } from "./navigationActions";
	import NavigationRow from "./NavigationRow.svelte";
	import CreateNavigationModal from "./CreateNavigationModal.svelte";
	import { SOURCES, TRIGGERS, dndzone } from "svelte-dnd-action";
	import { flip } from "svelte/animate";

    const flipDurationMs = 200;
	let dragDisabled = true;

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
    }

    function handleDelete(e: CustomEvent<number>) {
        items = items.filter(t => t.id !== e.detail);
    }

    function handleCreateVariant(e: CustomEvent<{id: number, variant: NavigationVariant}>) {
        items = items.map(t => {
            const newNavigation = t.id === e.detail.id ? 
                {...t, variants: [...t.variants, e.detail.variant]} : 
                t;
            return newNavigation;
        });
    }

    function handleUpdate(e: CustomEvent<Navigation>) {
        items = items.map(t => t.id === e.detail.id ? e.detail : t);
    }

    function saveOrder(newItems: Navigation[]) {
        saveSort(newItems.map(blog => blog.id));
    }

    function handleConsiderHeader(e: any) {
		const {items: newItems, info: {source, trigger}} = e.detail;
        items = [...newItems, ...items.filter(t => t.type === 'footer')];
		// Ensure dragging is stopped on drag finish via keyboard
		if (source === SOURCES.KEYBOARD && trigger === TRIGGERS.DRAG_STOPPED) {
			dragDisabled = true;
		}
	}

    function handleFinalizeHeader(e: any) {
		const {items: newItems, info: {source}} = e.detail;
        items = [...newItems, ...items.filter(t => t.type === 'footer')];
        saveOrder(newItems);

		// Ensure dragging is stopped on drag finish via pointer (mouse, touch)
		if (source === SOURCES.POINTER) {
			dragDisabled = true;
		}
	}

    function handleConsiderFooter(e: any) {
		const {items: newItems, info: {source, trigger}} = e.detail;
        items = [...newItems, ...items.filter(t => t.type === 'header')];
		// Ensure dragging is stopped on drag finish via keyboard
		if (source === SOURCES.KEYBOARD && trigger === TRIGGERS.DRAG_STOPPED) {
			dragDisabled = true;
		}
	}

    function handleFinalizeFooter(e: any) {
		const {items: newItems, info: {source}} = e.detail;
        items = [...newItems, ...items.filter(t => t.type === 'header')];
        saveOrder(newItems);

		// Ensure dragging is stopped on drag finish via pointer (mouse, touch)
		if (source === SOURCES.POINTER) {
			dragDisabled = true;
		}
	}

    function startDrag(e: any) {
		// preventing default to prevent lag on touch devices (because of the browser checking for screen scrolling)
		e.preventDefault();
		dragDisabled = false;
	}
	function handleKeyDown(e: any) {
		if ((e.key === "Enter" || e.key === " ") && dragDisabled) dragDisabled = false;
	}

    onMount(loadNavigation);

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
                <div class="nav-title">Header Navigation</div>
                    <Table columns="2fr 2fr 70px">

                        <TableRow head>
                            <div>Name</div>
                            <div>URL</div>
                            <div></div>
                        </TableRow>

                        <div
                            use:dndzone="{{ 
                                items: items.filter(t => t.type === 'header'),
                                dragDisabled, 
                                flipDurationMs,
                                dropTargetStyle: {
                                    outline: 'none',
                                    background: 'var(--hover)',
                                }
                            }}"
                            on:finalize={handleFinalizeHeader}
                            on:consider={handleConsiderHeader}
                        >

                            {#each items.filter(t => t.type === 'header') as navigation (navigation.id)}
                                <div class="nav-row"
                                    animate:flip="{{ duration: flipDurationMs }}"
                                >
                                    <button 
                                        class="dragger"
                                        style={dragDisabled ? 'cursor: grab' : 'cursor: grabbing'}
                                        tabindex={dragDisabled? 0 : -1} 
                                        aria-label="drag-handle"
                                        on:mousedown={startDrag}
                                        on:touchstart={startDrag}
                                        on:keydown={handleKeyDown}
                                        on:click={e => e.preventDefault()}
                                    >
                                        <IconGripVertical />
                                    </button>
                                    <NavigationRow 
                                        {navigation}
                                        on:delete={handleDelete}
                                        on:variantCreate={handleCreateVariant}
                                        on:update={handleUpdate}
                                    />
                                </div>
                            {/each}
                    </div>
                </Table>

                <div class="nav-title">Footer Navigation</div>
                    <Table columns="2fr 2fr 70px">

                        <TableRow head>
                            <div>Name</div>
                            <div>URL</div>
                            <div></div>
                        </TableRow>

                        <div
                            use:dndzone="{{ 
                                items: items.filter(t => t.type === 'footer'),
                                dragDisabled, 
                                flipDurationMs,
                                dropTargetStyle: {
                                    outline: 'none',
                                    background: 'var(--hover)',
                                }
                            }}"
                            on:finalize={handleFinalizeFooter}
                            on:consider={handleConsiderFooter}
                        >

                            {#each items.filter(t => t.type === 'footer') as navigation (navigation.id)}
                                <div class="nav-row"
                                    animate:flip="{{ duration: flipDurationMs }}"
                                >
                                    <button 
                                        class="dragger"
                                        style={dragDisabled ? 'cursor: grab' : 'cursor: grabbing'}
                                        tabindex={dragDisabled? 0 : -1} 
                                        aria-label="drag-handle"
                                        on:mousedown={startDrag}
                                        on:touchstart={startDrag}
                                        on:keydown={handleKeyDown}
                                        on:click={e => e.preventDefault()}
                                    >
                                        <IconGripVertical />
                                    </button>
                                    <NavigationRow 
                                        {navigation}
                                        on:delete={handleDelete}
                                        on:variantCreate={handleCreateVariant}
                                        on:update={handleUpdate}
                                    />
                                </div>
                            {/each}
                    </div>

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

    .nav-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .nav-row {
        padding: 10px 20px;
        display: block;
        align-items: center;
        cursor: pointer;
    }

    .nav-row:hover {
        background: var(--hover);
    }


    .dragger {
        padding: 0 5px;
        margin-right: 10px;
        position: relative;
        font-family: inherit;
    }

    .nav-row:hover {
        display: block;
    }

</style>
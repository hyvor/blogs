<script lang="ts">
	import { Button, ButtonGroup, FormControl, IconButton, IconMessage, LoadButton, Loader, Modal, SplitControl, Table, TableRow, TextInput, toast } from "@hyvor/design/components";
    import RedirectsModal from './RedirectsModal.svelte';
	import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconPlus, IconX } from "@hyvor/icons";
	import type { Redirect } from "../../../lib/types";
	import { getRedirect } from "./redirectActions";
	import { onMount } from "svelte";
	import RedirectRow from "./RedirectRow.svelte";
    import { dynamicRedirectsStore } from "./dynamicRedirect";

    let isCreating = false;

    let redirects : Redirect[] = [];
    let isLoading = true;
    let hasMore = false;
    let isLoadingMore = false;
    
    const limit = 25;

	let searchVal = '';
	let search = '';

    const searchActions = {
		onKeydown: (e: KeyboardEvent) => {
			if (e.key === 'Enter') {
				search = searchVal.trim();
				loadRedirect();
			}
            if (e.key === 'Escape') {
                searchActions.onClear();
            }
		},
		onClear: () => {
			searchVal = '';
			search = '';
			loadRedirect();
		}
	};
    function loadRedirect(more = false) {
        more ? isLoadingMore = true : isLoading = true;
        if (!more) redirects = [];

        getRedirect({
            search,
            limit,
            offset: more ? redirects.length : 0,
        })

            .then(res => {
                redirects = more ? [...redirects, ...res] : res;
                dynamicRedirectsStore.set(countDynamicRedirects(redirects));
                hasMore = res.length === limit;
            })
            .catch(err => {
                toast.error(err.message);
            })
            .finally(() => {
                isLoading = false;
                isLoadingMore = false;
            })
        }

    function countDynamicRedirects(redirects: Redirect[]) {
        return redirects.filter(r => r.dynamic).length;
    }

    function handleCreate(e: CustomEvent<Redirect>) {
        redirects = [e.detail, ...redirects];
        dynamicRedirectsStore.set(countDynamicRedirects(redirects));
    }

    function handleDelete(e: CustomEvent<number>) {
        redirects = redirects.filter(t => t.id !== e.detail);
        dynamicRedirectsStore.set(countDynamicRedirects(redirects));
    }

    function handleUpdate(e: CustomEvent<Redirect>) {
        redirects = redirects.map(t => t.id === e.detail.id ? e.detail : t);
        dynamicRedirectsStore.set(countDynamicRedirects(redirects));
    }

    onMount(loadRedirect)
</script>

<SettingsTop>

    <Button size="small" on:click={() => isCreating = true}>
        Add Redirect <IconPlus slot="end" />
    </Button>

    <div class="search-wrap">
        <TextInput
            bind:value={searchVal}
            placeholder="Search"
            style="width:200px;"
            on:keydown={searchActions.onKeydown}
            size="small"
        >
            <svelte:fragment slot="end">
                {#if searchVal.trim() !== ''}
                    <IconButton
                        variant="invisible"
                        color="gray"
                        size={16}
                        on:click={searchActions.onClear}
                    >
                        <IconX size={12} />
                    </IconButton>
                {/if}
            </svelte:fragment>
        </TextInput>

        {#if search !== searchVal}
            <span class="press-enter">
                ⏎
            </span>
        {/if}
    </div>

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

            <LoadButton
                text="Load more"
                show={hasMore}
                loading={isLoadingMore}
                on:click={() => loadRedirect(true)}
            />
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

<style lang="scss">
     .redirects {
        padding: 15px 30px;
        flex: 1;
        overflow: auto;
    }
    .search-wrap {
        display: flex;
        margin-left: 6px;
		.press-enter {
			color: var(--text-light);
			font-size: 14px;
			margin-left: 4px;
            margin-top: 6px;
		}
		:global(input) {
			font-size: 14px;
		}
	}
</style>
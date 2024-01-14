<script lang="ts">
	import { IconMessage, Loader, Table, TableRow, toast } from "@hyvor/design/components";
    import type { Import } from "../../../../lib/types";
	import { getImports } from "../importActions";
	import dayjs from "dayjs";

    let isLoading = true;

    export let imports: Import[] = [];

    getImports()
        .then(res => {
            imports = imports;
        })
        .catch(err => toast.error(err.message))
        .finally(() => isLoading = false);

</script>


{#if isLoading}
    <Loader padding={100} block />
{:else}

    {#if imports.length === 0}
        <IconMessage empty message="No imports found" padding={60} />
    {:else}

        <Table columns="3fr 1fr 1fr 1fr 1fr">

            <TableRow head>
                <div>Name/URL</div>
                <div>Type</div>
                <div>Date</div>
                <div>Status</div>
                <div>Counts</div>
            </TableRow>

            {#each imports as imp}
                <TableRow>
                    <div>{imp.name}</div>
                    <div>{imp.type}</div>
                    <div>{ dayjs.unix(imp.created_at).fromNow() }</div>
                    <div>{imp.status}</div>
                    <div>
                        
                    </div>
                </TableRow>
            {/each}

        </Table>

    {/if}

{/if}
<script lang="ts">
	import { Button, ButtonGroup, FormControl, Modal, SplitControl, Table, TableRow, TextInput } from "@hyvor/design/components";
    import RedirectsModal from './RedirectsModal.svelte';
	import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconPlus } from "@hyvor/icons";

    let isCreating = false;
</script>

<div class="settings">
    <SettingsTop>
        <Button on:click={() => isCreating = true}>
            Add Language <IconPlus slot="end" />
        </Button>
    </SettingsTop>

    <div class="table">
        <Table columns="1fr 1fr 1fr 70px">

            <TableRow head>
                <div>Matching Path</div>
                <div>Redirecting To</div>
                <div>Type</div>
                <div></div>
            </TableRow>
        </Table>
    </div>
</div>

{#if isCreating}
    <RedirectsModal bind:show={isCreating} />
{/if}

<style>
     .settings {
        flex: 1;
        overflow: auto;
        padding: 25px 30px;
    }

    .table {
        padding: 15px 30px;
    }
</style>
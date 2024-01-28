<script lang="ts">
	import { IconButton, Tooltip } from "@hyvor/design/components";
	import { IconArrowsCollapse, IconArrowsExpand, IconTrash } from "@hyvor/icons";
	import { deleteTable, mergeCells, splitCell } from "prosemirror-tables";
	import { postEditingStatusStore } from "../../../../../postStore";

    function handleMerge() {
        mergeCells(
            $postEditingStatusStore.editorView!.state,
            $postEditingStatusStore.editorView!.dispatch,
        );
        $postEditingStatusStore.editorView!.focus();
    }

    function handleSplit() {
        splitCell(
            $postEditingStatusStore.editorView!.state,
            $postEditingStatusStore.editorView!.dispatch,
        );
        $postEditingStatusStore.editorView!.focus();
    }

    function handleDelete() {
        deleteTable(
            $postEditingStatusStore.editorView!.state,
            $postEditingStatusStore.editorView!.dispatch,
        );
        $postEditingStatusStore.editorView!.focus();
    }

</script>

<div class="top">

    <Tooltip text="Merge cells">
        <IconButton size="small" color="input" on:click={handleMerge}>
            <IconArrowsCollapse size={14} />
        </IconButton>
    </Tooltip>

    <Tooltip text="Split cells">
        <IconButton size="small" color="input" on:click={handleSplit}>
            <IconArrowsExpand size={14} />
        </IconButton>
    </Tooltip>

    <Tooltip text="Delete Table">
        <IconButton size="small" color="input" on:click={handleDelete}>
            <IconTrash size={14} />
        </IconButton>
    </Tooltip>

</div>

<style>
    .top {
        margin-bottom: 15px;
        text-align: right;
    }
</style>
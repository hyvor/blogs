<script lang="ts">
  import { IconButton, Tooltip } from "@hyvor/design/components";
  import IconArrowsCollapse from "@hyvor/icons/IconArrowsCollapse";
  import IconArrowsExpand from "@hyvor/icons/IconArrowsExpand";
  import IconTrash from "@hyvor/icons/IconTrash";

  import { deleteTable, mergeCells, splitCell } from "prosemirror-tables";
  import { editorStore } from "../../store";

  function handleMerge() {
    mergeCells($editorStore.view.state, $editorStore.view.dispatch);
    $editorStore.view.focus();
  }

  function handleSplit() {
    splitCell($editorStore.view.state, $editorStore.view.dispatch);
    $editorStore.view.focus();
  }

  function handleDelete() {
    deleteTable($editorStore.view.state, $editorStore.view.dispatch);
    $editorStore.view.focus();
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

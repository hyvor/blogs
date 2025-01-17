<script lang="ts">
	import { Tooltip } from "@hyvor/design/components";
	import { IconInfoCircle } from "@hyvor/icons";

    export let label: string;
    export let info: string;
</script>

<span class="label">
    {label}
    <Tooltip text={info}>
        <IconInfoCircle size={14} />
    </Tooltip>
</span>

<style>
    .label {
        display:inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
    }
</style>
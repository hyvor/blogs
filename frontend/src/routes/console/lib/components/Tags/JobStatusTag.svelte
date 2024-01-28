<script lang="ts">
	import { Tag } from "@hyvor/design/components";
    import type { JobStatus } from "../../types";
    export let status: JobStatus;

    const color = {
        pending: 'blue',
        completed: 'green',
        failed: 'red'
    }[status] as any;

</script>


<Tag color={color} size="small">
    {status.toUpperCase()}
</Tag>

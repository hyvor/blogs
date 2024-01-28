<script lang="ts">
	import { Link, TableRow } from "@hyvor/design/components";
    import type { Export } from "../../../../lib/types";
	import JobStatusTag from "../../../../lib/components/Tags/JobStatusTag.svelte";
	import dayjs from "dayjs";

    export let data: Export;

    function fileNameFromUrl(url: string) {
        const parts = url.split('/');
        return parts[parts.length - 1];
    }
</script>

<TableRow>

    <div>Hyvor Blogs JSON</div>
    <div>{ dayjs.unix(data.created_at).fromNow() }</div>

    <div><JobStatusTag status={data.status} /></div>
    <div>
        {#if data.url}
            <Link 
                href={data.url} 
                download={fileNameFromUrl(data.url)}
            >Download</Link>
        {/if}
    </div>


</TableRow>
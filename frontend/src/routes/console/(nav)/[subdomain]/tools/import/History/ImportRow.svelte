<script lang="ts">
	import { IconButton, Modal, SplitControl, TableRow } from "@hyvor/design/components";
    import type { Import } from "../../../../../lib/types";
	import JobStatusTag from "../../../../../lib/components/Tags/JobStatusTag.svelte";
	import dayjs from "dayjs";
	import { IconThreeDots } from "@hyvor/icons";
    let showMore = $state(false);

    interface Props {
        data: Import;
    }

    let { data }: Props = $props();
</script>

<TableRow>
    <div>{data.name}</div>
    <div>{data.type}</div>
    <div>{ dayjs.unix(data.created_at).fromNow() }</div>
    <div>
        <JobStatusTag status={data.status} />
    </div>
    <div>
        { data.status === 'completed' ? data.imported_counts.posts + " posts" : '-' }
    </div>
    <div>
        <IconButton
            size={20} 
            variant="invisible"
            on:click={() => showMore = !showMore}
        >
            <IconThreeDots size={16} />
        </IconButton>
    </div>
</TableRow>



<Modal
    bind:show={showMore}
    title="Import Details"
    footer={{
        cancel: {
            text: "Close"
        },
        confirm: false
    }}
    on:close={() => showMore = false}
>

    <SplitControl label="Name">
        <span style="word-break:break-all">{data.name}</span>
    </SplitControl>

    <SplitControl label="Type">
        <span style="text-transform:capitalize">{data.type}</span>
    </SplitControl>

    <SplitControl label="Status">
        <JobStatusTag status={data.status} />
    </SplitControl>

    <SplitControl label="Started At">
        { dayjs.unix(data.created_at).format('YYYY-MM-DD HH:mm') }
    </SplitControl>

    <SplitControl label="Options">
        <pre>{JSON.stringify(data.options, null, 2)}</pre>
    </SplitControl>

    <SplitControl label="Imported Counts">
        <div class="imported-counts">
            <div>
                <span>Posts</span><span>{data.imported_counts.posts}</span>
            </div>
            <div>
                <span>Pages</span><span>{data.imported_counts.pages}</span>
            </div>
            <div>
                <span>Users</span><span>{data.imported_counts.users}</span>
            </div>
            <div>
                <span>Tags</span><span>{data.imported_counts.tags}</span>
            </div>
        </div>
    </SplitControl>

</Modal>

<style lang="scss">
    .imported-counts {
        div {
            margin-bottom: 10px;
        }
        span:first-child {
            display: inline-block;
            width: 100px;
            margin-right: 5px;
            font-weight: 600;
        }
    }
    pre {
        background-color: #fafafa;
        padding: 15px;
        border-radius: 20px;
        overflow: auto;
    }
</style>
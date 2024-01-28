<script lang="ts">
	import LinkStatusTag from "../Links/LinkStatusTag.svelte";

    /* export let name: string; */
    export let value: number;
    export let status: number;

</script>

<div class="stat">
    <div class='stat-count'>
        { 
            value
        }
    </div>
    <div class='name-icon'>
        <span class="icon">
            <LinkStatusTag status={status} />
        </span>
    </div>
</div>

<style lang="scss">
    .stat {
        padding: 15px 25px;
        text-align: center;
        .stat-count {
            font-size: 25px;
            font-weight: 600;
            margin-bottom: 5px;
        }
    }
</style>
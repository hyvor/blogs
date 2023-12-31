<script lang="ts">
    // @ts-ignore
    import { diffChars } from 'diff';

    export let strOld: string;
    export let strNew: string;

    $: diff = diffChars(strOld, strNew);

    function getColor(part: { added: boolean; removed: boolean }) {
        if (part.added) {
            return 'var(--green)';
        }

        if (part.removed) {
            return 'var(--red)';
        }

        return 'inherit';
    }

</script>

<span>
    {#each diff as part}
        <span style="color: {getColor(part)}">{part.value}</span>
    {/each}
</span>

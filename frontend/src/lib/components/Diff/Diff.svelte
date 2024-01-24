<script lang="ts">
    // @ts-ignore
    import { diffChars, diffSentences } from 'diff';

    export let strOld: string;
    export let strNew: string;
    export let sentences = false;
    export let onlyChanged = false;

    $: diff = sentences ? diffSentences(strOld, strNew) : diffChars(strOld, strNew);

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
        {#if !onlyChanged || (part.added || part.removed)}
            <span style="color: {getColor(part)}">{part.value}</span>
        {/if}
    {/each}
</span>

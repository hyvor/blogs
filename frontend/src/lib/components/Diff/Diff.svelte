<script lang="ts">
    // @ts-ignore
    import { diffChars, diffSentences } from 'diff';

    export let strOld: string;
    export let strNew: string;

    // $: diff = diffChars(strOld, strNew);
    $: diff = diffSentences(strOld, strNew);

    console.log(diff);

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
        {#if part.added || part.removed}
            <span style="color: {getColor(part)}">{part.value}</span>
        {/if}
    {/each}
</span>

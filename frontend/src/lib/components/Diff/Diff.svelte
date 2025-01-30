<script lang="ts">
    // @ts-ignore
    import { diffChars, diffSentences } from 'diff';

    interface Props {
        strOld: string;
        strNew: string;
        sentences?: boolean;
        onlyChanged?: boolean;
    }

    let {
        strOld,
        strNew,
        sentences = false,
        onlyChanged = false
    }: Props = $props();

    let diff = $derived(sentences ? diffSentences(strOld, strNew) : diffChars(strOld, strNew));

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

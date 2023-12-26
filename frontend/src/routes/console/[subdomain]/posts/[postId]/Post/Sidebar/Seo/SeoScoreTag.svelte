<script lang="ts">
    export let score: number;
    export let ignore = false;
    export let percentage = false;

    score = Math.round(score);
    const color = score < 50 ? 'red' : score < 80 ? 'orange' : 'green';

</script>

<span
    class={color}
    class:ignore={ignore}
>
    {ignore ? "?" : score}{percentage ? "%" : ''}
</span>


<style>

    span {
        display: inline-block;
        text-align: center;
        padding: 2px 6px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
        width: 34px;
    }

    span.green {
        background-color: var(--green-light);
        color: var(--green-dark);
    }
    span.orange {
        background: var(--orange-light);
        color: var(--orange-dark);
    }
    span.red {
        background: var(--red-light);
        color: var(--red-dark);
    }
    span.ignore {
        background-color: #ccc;
        color: #000;
    }

</style>
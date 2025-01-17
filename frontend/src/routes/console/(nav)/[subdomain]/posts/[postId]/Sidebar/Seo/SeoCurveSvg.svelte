<script lang="ts">

    export let perc: number;
    export let gradient = false;

    const radius = 65;
    $: dashArray = Math.PI * radius * perc / 100;

</script>

<svg width="200" height="120">
    <circle 
        cx="100" 
        cy="100" 
        r={radius} 
        stroke-linecap="round"
        stroke-dashoffset={-1 * Math.PI * radius}
        stroke-dasharray={`${dashArray} 100000`}
        stroke={gradient ? 'url(#score-gradient)' : '#e5e5e5'}
    ></circle>
    {#if gradient}
        <defs>
            <linearGradient id="score-gradient">
                <stop offset="0%" stop-color="#db7474" />
                <stop offset="25%" stop-color="#f1c40f" />
                <stop offset="100%" stop-color="#5d995d" />
            </linearGradient>
        </defs>
    {/if}
</svg>

<style>

    circle {
        fill: none;
        stroke-width:25px;
    }

</style>
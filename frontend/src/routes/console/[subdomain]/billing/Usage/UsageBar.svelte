<script lang="ts">
	import { onMount } from "svelte";
	import type { Usage } from "../../../lib/types";
	import byteFormatter from "../../../lib/helper/byte-formatter";

    export let name: string;
    export let data: Usage;
    export let bytes = false;
    export let zero = false;

    let width = "0%";
    const calcWidth = data.percentage + "%";

    onMount(() => {
        setTimeout(() => {
            width = calcWidth;
        }, 200);
    });

    let current = data.current as number | string;
    let total = data.total as number | string;
    if (bytes) {
        current = byteFormatter(data.current);
        total = byteFormatter(data.total);
    }

    let color = "var(--accent)";
    if (data.percentage > 99) {
        color = "var(--red-dark)";
    } else if (data.percentage > 85) {
        color = "var(--orange-dark)";
    }

</script>

<div class="usage-bar">
    <div class="usage-bar-top">
        <div class="usage-name">
            { name }
        </div>
        <div class="usage-number">
            <span 
                class="usage-now"
                style:color={color === 'var(--accent)' ? 'var(--text)' : color}
            >{current}</span>
            <span class="usage-full">/ {total === 0 && !zero ? "∞" : total}</span>
        </div>
    </div>
    <div class="usage-bar-bar">
        <div 
            class="usage-bar-fill"
            style:width={width}
            style:background={color}
        ></div>
    </div>
</div>

<style lang="scss">

    .usage-bar-top {
        display: flex;

        .usage-name {
            flex: 1;
            font-size: 14px;
        }
        .usage-now {
            margin-right: 4px;
            font-weight: 600;
        }

        .usage-full {
            color: var(--text-light);
            font-size: 12px;
        }
    }

    .usage-bar-bar {
        margin: 6px 0 15px;
        width:100%;
        height: 15px;
        background: var(--accent-light);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
    }
    .usage-bar-fill {
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        background: var(--accent);
        border-radius: 20px;
        transition: .3s width ease-out;
    }

</style>
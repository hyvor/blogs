<script lang="ts">
	import { Checkbox } from "@hyvor/design/components";

    export let levels : number[];
</script>

<div class="levels">
    {#each [1,2,3,4,5,6] as level}
        <div class="toc-level">
            h{level}
            <Checkbox
                checked={levels.includes(level)} 
                on:change={() => {
                    if (levels.includes(level)) {
                        levels = levels.filter(l => l !== level);
                    } else {
                        levels = [...levels, level];
                    }
                }}    
            />
        </div>
    {/each}
</div>

<style>
    .levels {
        display: flex;
        font-size: 16px;
        gap: 15px;
        align-items: center;
        border-top: 1px solid var(--border);
        padding: 15px 25px;
    }
    .toc-level {
        display: flex;
        gap: 5px;
        align-items: center;
    }
</style>
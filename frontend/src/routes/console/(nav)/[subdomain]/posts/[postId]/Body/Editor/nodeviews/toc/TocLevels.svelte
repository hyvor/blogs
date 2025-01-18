<script lang="ts">
	import { Checkbox } from "@hyvor/design/components";
	import { createEventDispatcher } from "svelte";

    interface Props {
        levels: number[];
    }

    let { levels = $bindable() }: Props = $props();

    const dispatch = createEventDispatcher();

    function handleChange(level: number) {

        let newLevels : number[];

        if (levels.includes(level)) {
            newLevels = levels.filter(l => l !== level);
        } else {
            newLevels = [...levels, level];
        }

        dispatch('change', newLevels);

        levels = newLevels;
    }

</script>

<div class="levels">
    {#each [1,2,3,4,5,6] as level}
        <div class="toc-level">
            h{level}
            <Checkbox
                checked={levels.includes(level)} 
                on:change={() => handleChange(level)}
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
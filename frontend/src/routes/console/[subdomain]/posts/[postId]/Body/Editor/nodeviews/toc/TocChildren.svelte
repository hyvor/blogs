<script lang="ts">
	import { Tag } from "@hyvor/design/components";
    import type { TocEntry } from "./toc";
    
    export let children: TocEntry[];
    export let top = false;
</script>

<div class="toc-ul" class:top>
    {#each children as child}
        <div class="toc-li">

            <div class="heading">

                <Tag size="x-small">
                    <strong>H{child.level}</strong>
                </Tag>

                <div class="title">{child.title}</div>

                <div class="dots"></div>

                {#if child.id}
                    <span class="id">#{child.id}</span>
                {:else}
                    <Tag color="orange" size="x-small">
                        No ID
                    </Tag>
                {/if}
            </div>



            {#if child.children.length > 0}
                <svelte:self children={child.children} />
            {/if}
        </div>
    {/each}
</div>

<style lang="scss">
    .toc-ul:not(.top) {
        padding-left: 25px;
    }
    .toc-li {
        display: flex;
        flex-direction: column;
    }

    .heading {
        display: flex;
        align-items: center;
        gap:6px;
        padding: 8px 10px;
        border-radius: 20px;
        cursor: pointer;
        &:hover {
            background-color: var(--hover);
        }
    }

    .title {
        font-size: 16px;
    }

    .dots {
        flex: 1;
        border-top: 1px dashed #ccc;
    }

    .id {
        font-size: 14px;
        color: #666;
    }

    .level {
        font-size: 14px;
        font-weight: 600;
    }

</style>
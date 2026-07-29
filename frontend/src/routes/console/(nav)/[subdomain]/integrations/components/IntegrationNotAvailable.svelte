<script lang="ts">
	import { IconMessage } from "@hyvor/design/components";
	import type { Snippet } from "svelte";

    interface Props {
        children: Snippet;
    }

    let { children }: Props = $props();
</script>

<div class="wrap">
    <div class="inner">
        {@render children()}
    </div>
</div>

<style>
    .wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        line-height: 1.5;
    }
    .inner {
        max-width: 600px;
        margin: 0 auto;
    }
</style>
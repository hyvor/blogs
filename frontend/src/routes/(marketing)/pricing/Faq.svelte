<script lang="ts">
	import type { ComponentType } from "svelte";

    interface Props {
        q: string;
        icon: ComponentType;
        children?: import('svelte').Snippet;
    }

    let { q, icon, children }: Props = $props();

    const SvelteComponent = $derived(icon);
</script>

<div class="faq">

    <SvelteComponent size={26} />
    <div class="q">{q}</div>
    <div class="a">{@render children?.()}</div>

</div>

<style>
    .faq {
        display: flex;
        flex-direction: column;
        width: calc(50% - 10px);
    }

    .q {
        font-size:20px;
        font-weight: 600;
        margin-top:10px;
    }
    .a {
        margin-top: 10px;
        line-height: 22px;
    }

    @media (max-width: 992px) {
        .faq {
            width: 100%;
        }
    }

</style>
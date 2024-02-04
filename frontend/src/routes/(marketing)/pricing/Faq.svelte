<script lang="ts">
	import type { ComponentType } from "svelte";

    export let q: string;
    export let icon: ComponentType;
</script>

<div class="faq">

    <svelte:component this={icon} size={26} />
    <div class="q">{q}</div>
    <div class="a"><slot /></div>

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
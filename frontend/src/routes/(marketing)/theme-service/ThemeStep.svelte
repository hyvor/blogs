<script lang="ts">
	import type { ComponentType } from "svelte";

    interface Props {
        icon: ComponentType;
        number: number;
        title: string;
        description: string;
    }

    let {
        icon,
        number,
        title,
        description
    }: Props = $props();

    const SvelteComponent = $derived(icon);
</script>

<div class="step">

    <div class="top">
        <div class="icon">
            <SvelteComponent size={50} />
        </div>
        <div class="top-line"></div>
        <div class="number">{number}</div>
    </div>

    <div class="title">{title}</div>
    <div class="description">{description}</div>


</div>

<style lang="scss">
    .step {
        display:flex;
        flex-direction: column;
        flex: 1;
        padding: 15px;
        position:relative;
    }
    .number {
        font-size: 60px;
        font-weight: 600;
        opacity: 0.25;
        color: var(--blue-dark);
    }
    .top {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .icon {
        color: var(--blue-dark);
        position:relative;
    }
    .top-line {
        height: 2px;
        background-color: var(--blue);
        opacity: 0.25;
        flex: 1;
    }
    .title {
        font-size: 20px;
        font-weight: 600;
        margin-top: 10px;
    }
    .description {
        margin-top: 10px;
        line-height: 22px;
    }
</style>
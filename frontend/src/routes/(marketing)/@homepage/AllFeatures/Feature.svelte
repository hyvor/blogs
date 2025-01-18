<script lang="ts">
	import type { ComponentType } from "svelte";

    interface Props {
        icon: ComponentType;
        title: string;
        description: string;
        color: 'green' | 'blue' | 'orange' | 'red';
    }

    let {
        icon,
        title,
        description,
        color
    }: Props = $props();

    const SvelteComponent = $derived(icon);
</script>   

<div class="feature">

    <div class="icon {color}">
        <SvelteComponent size={40} />
    </div>
    <div class="title">{title}</div>
    <div class="description">{description}</div>

</div>

<style>

    .feature {
        width: calc(33.33% - 15px);
    }

    .icon.green {
        color: var(--green);
    }
    .icon.blue {
        color: var(--blue);
    }
    .icon.orange {
        color: var(--orange);
    }
    .icon.red {
        color: var(--red);
    }
    

    .title {
        font-weight: 600;
        font-size: 20px;
        margin-top: 10px;
    }

    .description {
        font-size: 16px;
        margin-top: 10px;
    }

    @media (max-width: 992px) {
        .feature {
            width: 100%;
            text-align: center;
        }
    }

</style>
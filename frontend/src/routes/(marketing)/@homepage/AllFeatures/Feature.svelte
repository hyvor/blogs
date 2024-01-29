<script lang="ts">
	import type { ComponentType } from "svelte";

    export let icon: ComponentType;
    export let title: string;
    export let description: string;
    export let color: 'green' | 'blue' | 'orange' | 'red';
</script>   

<div class="feature">

    <div class="icon {color}">
        <svelte:component this={icon} size={40} />
    </div>
    <div class="title">{title}</div>
    <div class="description">{description}</div>

</div>

<style>

    .feature {
        width: calc(33.33% - 15px);
    }

    .icon {
        
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

</style>
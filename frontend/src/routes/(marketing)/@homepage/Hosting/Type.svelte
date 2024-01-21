<script lang="ts">
	import { Tag, TextInput } from "@hyvor/design/components";
	import { IconCheck, IconLock } from "@hyvor/icons";
	import CheckFeatures from "../../@components/CheckFeatures.svelte";

    export let name: string;
    export let domain: string;
    export let features : string[];

</script>


<div class="type hds-box">
    <div class="top">
        <span class="name">{name}</span>
        <Tag color="blue">{domain}</Tag>
    </div>

    <CheckFeatures {features} />

    <slot />

</div>

<style lang="scss">

    .type {
        padding: 20px 25px;
    }

    .top {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .name {
        font-weight: 600;
        font-size: 18px;
    }

</style>
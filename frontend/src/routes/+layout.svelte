<script lang="ts">
    import { Base } from "@hyvor/design/components";
    import '../app.css';

    import relativeTime from 'dayjs/plugin/relativeTime'
	import dayjs from "dayjs";
	import { onMount } from "svelte";
	import { setUpMarketing } from "./marketing";
    dayjs.extend(relativeTime);

    onMount(() => {
        setUpMarketing();
    })
</script>

<Base>
    <slot />
</Base>
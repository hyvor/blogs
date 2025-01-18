<script lang="ts">
    import { Base } from "@hyvor/design/components";
    import '../app.css';

    import relativeTime from 'dayjs/plugin/relativeTime'
	import dayjs from "dayjs";
	import { setUpMarketing } from "./marketing";
    interface Props {
        children?: import('svelte').Snippet;
    }

    let { children }: Props = $props();

    dayjs.extend(relativeTime);

    setUpMarketing();
</script>

<Base>
    {@render children?.()}
</Base>
<script lang="ts">
    import { Base } from "@hyvor/design/components";
    import '../app.css';

    import relativeTime from 'dayjs/plugin/relativeTime'
	import dayjs from "dayjs";
	import { setUpMarketing } from "./marketing";

    dayjs.extend(relativeTime);

    setUpMarketing();
</script>

<Base>
    <slot />
</Base>
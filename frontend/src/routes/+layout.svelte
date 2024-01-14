<script lang="ts">
    import { Base } from "@hyvor/design/components";
    import '../app.css';

    import relativeTime from 'dayjs/plugin/relativeTime'
	import dayjs from "dayjs";
    dayjs.extend(relativeTime);
</script>

<Base>
    <slot />
</Base>
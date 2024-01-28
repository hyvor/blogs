<script lang="ts">
	import dayjs from "dayjs";

    export let time: number; // Unix timestamp

    $: day = dayjs.unix(time);
</script>


<time 
    title={day.format("YYYY-MM-DD HH:mm:ss")}
    datetime={day.format()}
>{day.format('MMM D, YYYY')}</time>
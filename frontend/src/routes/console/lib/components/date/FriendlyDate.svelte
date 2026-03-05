<script lang="ts">
	import dayjs from 'dayjs';

	interface Props {
		time: number; // Unix timestamp
	}

	let { time }: Props = $props();

	let day = $derived(dayjs.unix(time));
</script>

<time title={day.format('YYYY-MM-DD HH:mm:ss')} datetime={day.format()}
	>{day.format('MMM D, YYYY')}</time
>

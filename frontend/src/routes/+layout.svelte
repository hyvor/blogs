<script lang="ts">
	import { Base } from '@hyvor/design/components';
	import '../app.css';

	import relativeTime from 'dayjs/plugin/relativeTime';
	import dayjs from 'dayjs';
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	dayjs.extend(relativeTime);
</script>

<Base>
	{@render children?.()}
</Base>

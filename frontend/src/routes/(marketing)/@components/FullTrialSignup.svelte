<script lang="ts">
	import { FullTrialSignup } from '@hyvor/design/marketing';

	interface Props {
		title?: string;
		[key: string]: any;
	}

	let { title = 'Start your blog today', ...rest }: Props = $props();
</script>

<FullTrialSignup
	badge="Open-source & free to try"
	{title}
	description="Join hundreds of bloggers and businesses building with Hyvor Blogs."
	button={{ href: '/console?signup', label: 'Start your blog →' }}
	{...rest}
/>

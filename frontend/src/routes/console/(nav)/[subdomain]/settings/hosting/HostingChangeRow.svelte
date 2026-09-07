<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import type { HostingChange, HostingChangeAt } from '../../../../lib/types';

	interface Props {
		change: HostingChange;
	}

	let { change }: Props = $props();

	const hostingAtLabels: Record<HostingChangeAt, string> = {
		subdomain: 'Subdomain',
		domain: 'Custom Domain',
		self: 'Self-Serving'
	};

	let calloutConfig: {
		type: 'info' | 'success' | 'danger';
		content: string;
	} = $derived.by(() => {
		if (change.status === 'changing') {
			return {
				type: 'info',
				content:
					'Your hosting change is being applied. This may take a few minutes depending on the number of posts on your blog. You can leave this page and come back later to check the status.'
			};
		} else if (change.status === 'success') {
			return {
				type: 'success',
				content: 'Your hosting change has been successfully applied.'
			};
		}

		return {
			type: 'danger',
			content: 'An unknown error occurred while applying your hosting change.'
		};
	});
</script>

<div class="wrap">
	<div class="callout">
		<Callout type={calloutConfig.type}>
			{calloutConfig.content}
		</Callout>
	</div>
	<div class="transition">
		<a href={change.from_url} target="_blank">{change.from_url}</a>
		({hostingAtLabels[change.from_at]}) &rarr;
		<a href={change.to_url} target="_blank">{change.to_url}</a>
		({hostingAtLabels[change.to_at]})
	</div>
	<div class="date">
		{new Date(change.created_at * 1000).toLocaleString(undefined, {
			dateStyle: 'medium',
			timeStyle: 'short'
		})}
	</div>
</div>

<style>
	.wrap {
		border: 1px solid var(--border);
		padding: 16px;
		border-radius: var(--box-radius);
	}
	.callout {
		font-size: 14px;
		margin-bottom: 10px;
		line-height: 14px;
	}
	.date {
		font-size: 12px;
		color: var(--text-light);
		margin-top: 2px;
	}
	.transition {
		line-height: normal;
		font-size: 14px;
	}
	a {
		text-decoration: underline;
	}
</style>

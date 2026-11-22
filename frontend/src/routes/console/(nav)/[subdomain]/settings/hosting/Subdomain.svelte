<script lang="ts">
	import { SplitControl } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { hostingInfoStore } from '../../../../lib/stores/blogStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
</script>

<!-- the subdomain cannot be changed: media is always served from it -->
<SplitControl
	column
	label={i18n.t('console.settings.hosting.subdomain')}
	caption={i18n.t('console.settings.hosting.subdomainCaption')}
>
	<a class="subdomain-url" href={$hostingInfoStore.subdomain_url} target="_blank" rel="noopener">
		<span>{$hostingInfoStore.subdomain_url}</span>
		<IconBoxArrowUpRight size={12} />
	</a>
</SplitControl>

<style>
	.subdomain-url {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		font-weight: 500;
		word-break: break-all;
	}
	.subdomain-url:hover {
		text-decoration: underline;
	}
</style>

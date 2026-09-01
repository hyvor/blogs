<script lang="ts">
	import { Table, TableRow, toast } from '@hyvor/design/components';
	import { parse } from 'tldts';
	import { getConfig } from '../../../../lib/config';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		domain: string;
	}

	let { domain = '' }: Props = $props();

	const CLOUD_CNAME_DOMAIN = 'hyvorblogs.io';

	let host = $derived.by(() => {
		const { subdomain } = parse(domain);
		return subdomain || '@';
	});

	function copy(value: string) {
		navigator.clipboard.writeText(value);
		toast.success(i18n.t('console.settings.hosting.copiedToClipboard'));
	}
</script>

<Table columns="1fr 1fr 1fr" style="bordered">
	<TableRow head>
		<div>{i18n.t('console.settings.hosting.dnsType')}</div>
		<div>{i18n.t('console.settings.hosting.dnsHost')}</div>
		<div>{i18n.t('console.settings.hosting.dnsContent')}</div>
	</TableRow>
	<TableRow>
		<div>CNAME</div>
		<div>
			{host}
		</div>
		<div>{
			getConfig().deployment === 'cloud' ?
				CLOUD_CNAME_DOMAIN :
				getConfig().domains.app
			}</div>
	</TableRow>
</Table>

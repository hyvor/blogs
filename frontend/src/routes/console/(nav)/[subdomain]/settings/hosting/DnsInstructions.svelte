<script lang="ts">
	import { TabNav, TabNavItem, Table, TableRow, Tag, toast } from '@hyvor/design/components';
	import { parse } from 'tldts';
	import { getConfig, loadConfig } from '../../../../lib/config';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		// the full custom domain (e.g. "blog.example.com"). When omitted, generic
		// placeholder examples are shown instead (used in the docs page).
		domain?: string;
	}

	let { domain = '' }: Props = $props();

	const CLOUD_CNAME_DOMAIN = 'hyvorblogs.io';
	const CLOUD_IP = '116.202.185.2';

	let dnsMethod: 'cname' | 'a' = $state('cname');
	let configLoaded = $state(false);

	const records = [
		{
			type: 'A',
			host: '@',
			content: CLOUD_IP
		},
		{
			type: 'AAAA',
			host: '5.5.5.5',
			content: '::'
		}
	];

	$effect(() => {
		loadConfig().then(() => {
			configLoaded = true;
		});
	});

	const deployment = $derived(configLoaded ? getConfig().deployment : 'cloud');

	const cnameTarget = $derived(
		deployment === 'on-prem' ? getConfig().domains.app : CLOUD_CNAME_DOMAIN
	);

	const host = $derived.by(() => {
		if (!domain) {
			return null;
		}
		const { subdomain } = parse(domain);
		return subdomain || '@';
	});

	function copy(value: string) {
		navigator.clipboard.writeText(value);
		toast.success(i18n.t('console.settings.hosting.copiedToClipboard'));
	}
</script>

<TabNav>
	<TabNavItem name="cname" active={dnsMethod === 'cname'} onclick={() => (dnsMethod = 'cname')}>
		CNAME {#snippet end()}
			<Tag size="small" color="blue">Preferred</Tag>
		{/snippet}
	</TabNavItem>
	<TabNavItem name="a" active={dnsMethod === 'a'} onclick={() => (dnsMethod = 'a')}
		>A Record</TabNavItem
	>
</TabNav>

<Table columns="1fr 1fr 1fr" style="bordered">
	<TableRow head>
		<div>{i18n.t('console.tools.import.type')}</div>
		<div>Host/Name</div>
		<div>{i18n.t('console.tools.import.content')}</div>
	</TableRow>
	{#each records as record}
		<TableRow>
			<div>{record.type}</div>
			<div>{record.host}</div>
			<div>{record.content}</div>
		</TableRow>
	{/each}
</Table>

<!-- {#if dnsMethod === 'cname'}
	<Table columns="1fr 2fr">
		<br />
		<TableRow head>
			<div>Field</div>
			<div>Value</div>
		</TableRow>
		<TableRow>
			<div>Host/Name</div>
			<div>
				{#if host !== null}
					<code>{host}</code> for <strong>{domain}</strong>
				{:else}
					<div style="margin-bottom:6px;">
						<code>@</code> for <strong>example.com</strong> or
					</div>
					<code>blog</code> for <strong>blog.example.com</strong>
				{/if}
			</div>
		</TableRow>
		<TableRow>
			<div>{i18n.t('console.tools.import.content')}</div>
			<div>
				<code>{cnameTarget}</code>
				<Button
					size="x-small"
					on:click={() => copy(cnameTarget)}
					style="margin-left:5px;"
					color="input"
				>
					Copy {#snippet end()}
						<IconCopy size={12} />
					{/snippet}
				</Button>
			</div>
		</TableRow>
	</Table>
{:else}
	<Table columns="1fr 2fr">
		<br />
		<TableRow head>
			<div>Field</div>
			<div>Value</div>
		</TableRow>
		<TableRow>
			<div>Host/Name</div>
			<div>
				{#if host !== null}
					<code>{host}</code> for <strong>{domain}</strong>
				{:else}
					<div style="margin-bottom:6px;">
						<code>@</code> for <strong>example.com</strong> or
					</div>
					<code>blog</code> for <strong>blog.example.com</strong>
				{/if}
			</div>
		</TableRow>
		<TableRow>
			<div>IP Address</div>
			<div>
				{#if deployment === 'on-prem'}
					Contact your server admin for the IP address to use.
				{:else}
					<code>{CLOUD_IP}</code>
					<Button
						size="x-small"
						on:click={() => copy(CLOUD_IP)}
						style="margin-left:5px;"
						color="input"
					>
						Copy {#snippet end()}
							<IconCopy size={12} />
						{/snippet}
					</Button>
				{/if}
			</div>
		</TableRow>
	</Table>
{/if} -->

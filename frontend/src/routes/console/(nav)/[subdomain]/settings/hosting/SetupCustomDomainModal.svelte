<script lang="ts">
	import {
		Button,
		ButtonGroup,
		FormControl,
		Modal,
		SplitControl,
		TextInput,
		Validation,
		toast,
		TabNav,
		TabNavItem,
		Table,
		TableRow,
		Tag,
		confirm
	} from '@hyvor/design/components';
	import IconCopy from '@hyvor/icons/IconCopy';
	import {
		createCustomDomainSetup,
		deleteCustomDomainSetup,
		updateCustomDomainSetup,
		verifyCustomDomainSetup
	} from './hostingActions';
	import { hostingInfoStore } from '../../../../lib/stores/blogStore';

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let oldDomain = $state('');
	let domain = $state('');
	let isEditing = $state(!$hostingInfoStore.custom_domain);
	let error: string | null = $state(null);
	let loading = $state(false);

	let dnsMethod: 'cname' | 'a' = $state('cname');
	const CUSTOM_DOMAIN_IP = '116.202.185.2';
	const CNAME_DOMAIN = 'hyvorblogs.io';

	let domainInput: HTMLInputElement;

	$effect(() => {
		if (show) {
			domain = $hostingInfoStore.custom_domain?.domain || '';
			error = null;
			domainInput?.focus();
		}
	});

	async function handleNext() {
		error = null;
		const domainTrimmed = domain.trim();

		if ($hostingInfoStore.custom_domain?.domain === domainTrimmed) {
			// No changes, proceed to verification
			isEditing = false;
			return;
		}

		if (domainTrimmed === '') {
			error = 'Custom Domain is required';
			return;
		}
		if (domainTrimmed.match(' ')) {
			error = 'Custom Domain cannot contain spaces';
			return;
		}
		if (domainTrimmed.match(/^https?:\/\//)) {
			error = 'Add the domain without the protocol (https://)';
			return;
		}
		if (domainTrimmed.match('/')) {
			error =
				'Custom Domain cannot contain /. Use self-hosting to host your blog in a subdirectory';
			return;
		}

		loading = true;
		const saveToastId = toast.loading('Saving custom domain...');

		try {
			let customDomainSetup;

			if ($hostingInfoStore.custom_domain) {
				// Custom domain setup exists
				customDomainSetup = await updateCustomDomainSetup(oldDomain, domainTrimmed);
				toast.success('Custom domain updated!', { id: saveToastId });
			} else {
				// New custom domain setup
				customDomainSetup = await createCustomDomainSetup(domainTrimmed);
				toast.success('Custom domain saved!', { id: saveToastId });
			}

			hostingInfoStore.update((info) => ({ ...info, custom_domain: customDomainSetup }));
			oldDomain = domainTrimmed;
			isEditing = false;
		} catch (err: any) {
			// TODO: handle already taken domains
			// if (err.message === 'domain_taken') {
			// 	error = 'This domain is already taken by another blog. Contact support if needed.';
			// 	toast.error('Failed to save domain: domain taken', { id: saveToastId });
			// } else {
			toast.error(err.message || 'Failed to save domain', { id: saveToastId });
			// }
			// return false;
		}
		loading = false;
	}

	async function handleVerify() {
		loading = true;
		const verifyToastId = toast.loading('Verifying DNS records...');

		try {
			const customDomainSetup = await verifyCustomDomainSetup();
			if (customDomainSetup.status === 'active') {
				toast.success('Custom domain is verified and active!', { id: verifyToastId });
				show = false;
			} else {
				toast.warning('DNS verification is pending. Please configure your DNS settings.', {
					id: verifyToastId
				});
			}
		} catch (err: any) {
			toast.error(err.message || 'Failed to verify DNS records', { id: verifyToastId });
		}
		loading = false;
	}

	function handleEditDomain() {
		isEditing = true;
	}

	async function handleAbort() {
		const confirmAbort = await confirm({
			title: 'Abort Custom Domain Setup',
			content:
				'Are you sure you want to abort the custom domain setup? This will revert back to default subdomain setup (hyvorblogs.io) and you will have to start from scratch if you want to set it up again.',
			confirmText: 'Yes, Abort',
			cancelText: 'No, Keep It',
			danger: true
		});

		if (!confirmAbort) {
			return;
		}

		loading = true;
		const abortToastId = toast.loading('Aborting custom domain setup...');

		await deleteCustomDomainSetup()
			.then(() => {
				hostingInfoStore.update((info) => ({ ...info, custom_domain: null }));
				toast.success('Custom domain setup aborted', { id: abortToastId });
				isEditing = true;
				show = false;
			})
			.catch((err) => {
				toast.error(err.message || 'Failed to abort custom domain setup', { id: abortToastId });
			})
			.finally(() => {
				loading = false;
			});
	}
</script>

<Modal title="Set-up Custom Domain" {loading} bind:show>
	<SplitControl label="Custom Domain" caption="Your custom domain name" noHorizonalPadding>
		<FormControl>
			<TextInput
				bind:value={domain}
				bind:input={domainInput}
				placeholder="blog.example.com"
				block
				state={error ? 'error' : undefined}
				readonly={!isEditing}
				autofocus
			/>
			{#if error}
				<Validation state="error">{error}</Validation>
			{/if}
		</FormControl>
	</SplitControl>
	{#if !isEditing}
		<p>
			Your custom domain needs to be verified. Please update your DNS records as shown below to
			verify your domain ownership.
		</p>

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

		{#if dnsMethod === 'cname'}
			<Table columns="1fr 2fr">
				<br />
				<TableRow head>
					<div>Field</div>
					<div>Value</div>
				</TableRow>
				<TableRow>
					<div>Host/Name</div>
					<div>
						<div style="margin-bottom:6px;">
							<code>@</code> for <strong>example.com</strong> or
						</div>
						<code>blog</code> for <strong>blog.example.com</strong>
					</div>
				</TableRow>
				<TableRow>
					<div>Content</div>
					<div>
						<code>{CNAME_DOMAIN}</code>
						<Button
							size="x-small"
							on:click={() => {
								navigator.clipboard.writeText(CNAME_DOMAIN);
								toast.success('Copied to clipboard');
							}}
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
						<div style="margin-bottom:6px;">
							<code>@</code> for <strong>example.com</strong> or
						</div>
						<code>blog</code> for <strong>blog.example.com</strong>
					</div>
				</TableRow>
				<TableRow>
					<div>IP Address</div>
					<div>
						<code>{CUSTOM_DOMAIN_IP}</code>
						<Button
							size="x-small"
							on:click={() => {
								navigator.clipboard.writeText(CUSTOM_DOMAIN_IP);
								toast.success('Copied to clipboard');
							}}
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
		{/if}
	{/if}

	{#snippet footer()}
		{#if !isEditing}
			<ButtonGroup>
				<Button variant="invisible" on:click={handleEditDomain} disabled={loading}
					>Edit Domain</Button
				>
				<Button variant="fill-light" color="red" on:click={handleAbort} disabled={loading}
					>Abort</Button
				>
				<Button on:click={handleVerify} disabled={loading}>Verify Now</Button>
			</ButtonGroup>
		{:else}
			<ButtonGroup>
				<Button variant="invisible" on:click={() => (show = false)} disabled={loading}
					>Cancel</Button
				>
				<Button on:click={handleNext} disabled={loading}>Next</Button>
			</ButtonGroup>
		{/if}
	{/snippet}
</Modal>

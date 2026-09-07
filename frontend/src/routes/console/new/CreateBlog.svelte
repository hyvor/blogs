<script lang="ts">
	import { goto } from '$app/navigation';
	import {
		Callout,
		FormControl,
		Link,
		SplitControl,
		Switch,
		TextInput,
		Tooltip,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { ResourceCreator } from '@hyvor/design/cloud';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';

	import { addToBlogList, blogListStore, resolvedLicenseStore } from '../lib/stores';
	import { createBlog, getSubdomainAvailable } from '../lib/actions/blogActions';
	import type { BlogList } from '../lib/types';
	import { getConfig } from '../lib/config';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import { getI18n } from '../lib/i18n';

	const i18n = getI18n();

	interface Props {
		dev?: boolean;
	}

	let { dev = false }: Props = $props();

	let name = $state('');
	let subdomain = $state('');

	let subdomainEdited = false;

	let nameError: string | null = $state(null);
	let subdomainError: string | null = $state(null);
	let subdomainSuccess: string | null = $state(null);

	let subdomainCheckTimeout: null | ReturnType<typeof setTimeout> = null;
	let subdomainCheckAbortController: AbortController | null = null;

	let hyvorTalk = $state(true);
	let hyvorPost = $state(true);

	function checkSubdomain() {
		if (subdomainCheckTimeout) {
			clearTimeout(subdomainCheckTimeout);
		}
		if (subdomainCheckAbortController) {
			subdomainCheckAbortController.abort();
		}

		subdomainError = null;
		subdomainSuccess = null;

		if (!subdomain) return;

		subdomainCheckTimeout = setTimeout(() => {
			subdomainCheckAbortController = new AbortController();

			getSubdomainAvailable(subdomain).then((res) => {
				if (res.available) {
					subdomainSuccess = 'Subdomain is available';
				} else {
					subdomainError = 'Subdomain is already taken';
				}
			});
		}, 500);
	}

	$effect(() => {
		subdomain;
		checkSubdomain();
	});

	function handleBack() {
		if ($blogListStore.length > 0) {
			goto('/console');
		} else {
			goto('/');
		}
	}

	function handleNameInput(e: any) {
		nameError = null;

		const value = e.target.value;

		if (!subdomainEdited) {
			subdomain = value
				.toLowerCase()
				.replace(/[^a-z0-9-]/g, '-')
				.replace(/-+/g, '-')
				.replace(/(^-|-$)/g, '');
		}
	}

	function handleSubdomainInput() {
		subdomainEdited = true;
	}

	async function handleCreate() {
		let valid = true;

		if (name.trim() === '') {
			nameError = i18n.t('console.common.nameRequired');
			valid = false;
		}

		if (subdomain.trim() === '') {
			subdomainError = 'Subdomain is required';
			valid = false;
		}

		if (!valid) {
			return false;
		}

		let blog: BlogList;
		try {
			const res = await createBlog(name, subdomain, dev, hyvorTalk, hyvorPost);
			addToBlogList(res.blog);
			blog = res.blog;

			if (res.resolved_license) {
				resolvedLicenseStore.set(res.resolved_license);
			}

			res.warnings.forEach((warning) => {
				toast.warning(warning);
			});
		} catch (e: any) {
			toast.error(e.message);
			return false;
		}

		goto('/console/' + blog.subdomain);

		return true;
	}
</script>

<ResourceCreator
	title="Start a blog"
	resourceTitle="Blog"
	cta="Create Blog"
	onback={handleBack}
	oncreate={handleCreate}
	ctaDisabled={name.trim() === '' || subdomain.trim() === ''}
>
	{#if dev}
		<div style="margin-bottom:20px;">
			<Callout type="warning">
				{#snippet icon()}
					<IconExclamationCircle />
				{/snippet}
				{#snippet title()}
					<div>Development Blog</div>
				{/snippet}
				<div>
					You are creating a development blog, which can only be used for theme development. Click <Link
						href="/console/new">here</Link
					> to create a production blog.
				</div>
			</Callout>
		</div>
	{/if}

	<SplitControl
		label={i18n.t('console.common.name')}
		caption="A name for your blog"
		noHorizonalPadding
	>
		<FormControl>
			<TextInput
				block
				bind:value={name}
				oninput={handleNameInput}
				maxlength={50}
				state={nameError ? 'error' : undefined}
				autofocus
			/>

			{#if nameError}
				<Validation state="error">
					{nameError}
				</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	{#if !dev}
		<SplitControl
			label={i18n.t('console.settings.hosting.subdomain')}
			caption="Only a-z, 0-9, and hyphens (-)"
			noHorizonalPadding
		>
			<FormControl>
				<TextInput
					block
					bind:value={subdomain}
					oninput={handleSubdomainInput}
					maxlength={50}
					state={subdomainError ? 'error' : subdomainSuccess ? 'success' : undefined}
				>
					{#snippet end()}
						.hyvorblogs.io
					{/snippet}
				</TextInput>

				{#if subdomainError}
					<Validation state="error">
						{subdomainError}
					</Validation>
				{/if}

				{#if subdomainSuccess}
					<Validation state="success">
						{subdomainSuccess}
					</Validation>
				{/if}
			</FormControl>
		</SplitControl>

		{#if getConfig().deployment === 'cloud'}
			<SplitControl noHorizonalPadding caption="Comments Integration">
				{#snippet label()}
					<span class="product-brand">
						<img src="/img/services/hyvor-talk.svg" alt="Hyvor Talk" width="18" />
						Hyvor Talk
						<Tooltip
							text="All Hyvor Blogs plans include a free complimentary license to Hyvor Talk. Disable this if you want to use a different commenting system."
						>
							<IconInfoCircle size={14} />
						</Tooltip>
					</span>
				{/snippet}

				<Switch bind:checked={hyvorTalk} />
			</SplitControl>
			<SplitControl noHorizonalPadding caption="Newsletter Integration">
				{#snippet label()}
					<span class="product-brand">
						<img src="/img/services/hyvor-post.svg" alt="Hyvor Post" width="18" />
						Hyvor Post

						<Tooltip
							text="All Hyvor Blogs plans include a free complimentary license to Hyvor Post. Disable this if you want to use a different newsletter platform."
						>
							<IconInfoCircle size={14} />
						</Tooltip>
					</span>
				{/snippet}

				<Switch bind:checked={hyvorPost} />
			</SplitControl>
		{/if}
	{/if}
</ResourceCreator>

<style>
	.product-brand {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		font-weight: 600;
	}
</style>

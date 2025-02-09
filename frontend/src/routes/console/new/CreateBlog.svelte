<script lang="ts">
	import { goto } from '$app/navigation';
	import {
		Button,
		Callout,
		FormControl,
		Link,
		Loader,
		SplitControl,
		TextInput,
		Validation,
		toast,
		hyvorBar
	} from '@hyvor/design/components';
	import IconCaretLeft from '@hyvor/icons/IconCaretLeft';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';

	import { addToBlogList, blogListStore } from '../lib/stores';
	import { createBlog, getSubdomainAvailable } from '../lib/actions/blogActions';

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

	let isCreating = $state(false);

	let subdomainCheckTimeout: null | ReturnType<typeof setTimeout> = null;
	let subdomainCheckAbortController: AbortController | null = null;

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

	function handleCreate() {
		let valid = true;

		if (name.trim() === '') {
			nameError = 'Name is required';
			valid = false;
		}

		if (subdomain.trim() === '') {
			subdomainError = 'Subdomain is required';
			valid = false;
		}

		if (!valid) {
			return;
		}

		isCreating = true;

		createBlog(name, subdomain, dev)
			.then((res) => {
				toast.success('Blog created successfully');
				addToBlogList(res);

				const event = new CustomEvent('console:blog:created', { detail: res });
				window.dispatchEvent(event);

				goto('/console/' + res.subdomain);
				hyvorBar.reload();
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				isCreating = false;
			});
	}
</script>

<div class="wrap">
	<div class="inner hds-box">
		<div class="back">
			<Button variant="outline" size="small" on:click={handleBack} disabled={isCreating}>
				{#snippet start()}
					<IconCaretLeft size={14} />
				{/snippet}
				Back
			</Button>
		</div>

		{#if isCreating}
			<Loader block padding={130}>Creating your blog...</Loader>
		{:else}
			<div class="title">Start a new blog</div>

			<div class="form">
				{#if dev}
					<Callout type="warning" style="margin-bottom:20px;">
						{#snippet icon()}
							<IconExclamationCircle />
						{/snippet}
						{#snippet title()}
							<div>Development Blog</div>
						{/snippet}
						<div>
							You are creating a development blog, which can only be used for theme
							development. Click <Link href="/console/new">here</Link> to create a production
							blog.
						</div>
					</Callout>
				{/if}

				<SplitControl label="Name" caption="A name for your blog">
					<FormControl>
						<TextInput
							block
							bind:value={name}
							on:input={handleNameInput}
							maxlength="50"
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
					<SplitControl label="Subdomain" caption="Only a-z, 0-9, and hyphens (-)">
						<FormControl>
							<TextInput
								block
								bind:value={subdomain}
								on:input={handleSubdomainInput}
								maxlength="50"
								state={subdomainError
									? 'error'
									: subdomainSuccess
										? 'success'
										: undefined}
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
				{/if}
			</div>

			<div class="footer">
				<Button size="large" on:click={handleCreate}>Create Blog</Button>
			</div>
		{/if}
	</div>
</div>

<style>
	.back {
		position: absolute;
		bottom: 100%;
		left: 0;
		padding: 15px 0;
	}
	.wrap {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		width: 100%;
		height: 100vh;
	}
	.title {
		padding: 25px;
		font-weight: 600;
		font-size: 22px;
		text-align: center;
	}
	.inner {
		width: 550px;
		max-width: 100%;
		position: relative;
	}
	.form {
		padding: 0 20px;
	}
	.footer {
		padding: 20px;
		padding-bottom: 30px;
		text-align: center;
	}
</style>

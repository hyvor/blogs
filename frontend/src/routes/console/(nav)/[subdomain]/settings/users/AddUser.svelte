<script lang="ts">
	import {
		FormControl,
		InputGroup,
		Link,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import type { User, UserRole } from '../../../../lib/types';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { createGuestUser, createHyvorUser } from './userActions';
	import { createEventDispatcher } from 'svelte';
	import { OrganizationMemberSearch } from '@hyvor/design/cloud';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let type: 'hyvor' | 'guest' = $state('hyvor');

	let role: UserRole = $state('admin');
	let hyvorUserId: number | undefined = $state(undefined);

	let guestName = $state('');
	let guestNameError: null | string = $state(null);
	let guestNameEl: HTMLInputElement | undefined = $state();

	let isLoading = $state(false);

	const dispatch = createEventDispatcher<{ add: User }>();

	function handleAdd() {
		type === 'hyvor' ? addHyvor() : addGuest();
	}

	function addHyvor() {
		if (!hyvorUserId) {
			return;
		}

		isLoading = true;

		createHyvorUser(hyvorUserId, role)
			.then((res) => {
				dispatch('add', res);
				show = false;
				toast.success(i18n.t('console.settings.users.added'));
			})
			.catch((e) => {
				toast.error(e.message || i18n.t('console.settings.users.failedToAdd'));
			})
			.finally(() => {
				isLoading = false;
			});
	}

	function addGuest() {
		guestNameError = null;

		if (guestName.trim() === '') {
			guestNameError = i18n.t('console.settings.users.nameRequired');
			guestNameEl?.focus();
			return;
		}

		isLoading = true;

		createGuestUser(guestName)
			.then((res) => {
				dispatch('add', res);
				show = false;
				toast.success(i18n.t('console.settings.users.added'));
			})
			.catch((e) => {
				toast.error(e.message || i18n.t('console.settings.users.failedToAdd'));
			})
			.finally(() => {
				isLoading = false;
			});
	}
</script>

<Modal
	bind:show
	title={i18n.t('console.settings.users.add')}
	footer={{
		confirm: {
			text: i18n.t('console.settings.users.add'),
			props: {
				disabled: type === 'hyvor' && hyvorUserId === undefined
			}
		}
	}}
	loading={isLoading}
	on:confirm={handleAdd}
	closeOnOutsideClick={false}
>
	<SplitControl
		label={i18n.t('console.settings.users.userType')}
		caption={i18n.t('console.settings.users.userTypeCaption')}
	>
		<InputGroup>
			<Radio bind:group={type} value="hyvor">HYVOR</Radio>
			<Radio bind:group={type} value="guest">
				{i18n.t('console.settings.users.status.guest')}
			</Radio>
		</InputGroup>
	</SplitControl>

	{#if type === 'hyvor'}
		<SplitControl label={i18n.t('console.settings.users.user')}>
			<OrganizationMemberSearch bind:selectedUserId={hyvorUserId} />
		</SplitControl>
		<SplitControl
			label={i18n.t('console.settings.users.role')}
			caption={i18n.t('console.settings.users.roleCaption')}
		>
			<div class="roles">
				<div>
					<Radio bind:group={role} value="admin">
						{i18n.t('console.settings.users.roles.admin')}
					</Radio>
					<span>{i18n.t('console.settings.users.roleDesc.admin')}</span>
				</div>
				<div>
					<Radio bind:group={role} value="editor">
						{i18n.t('console.settings.users.roles.editor')}
					</Radio>
					<span>{i18n.t('console.settings.users.roleDesc.editor')}</span>
				</div>
				<div>
					<Radio bind:group={role} value="writer">
						{i18n.t('console.settings.users.roles.writer')}
					</Radio>
					<span>{i18n.t('console.settings.users.roleDesc.writer')}</span>
				</div>
				<div>
					<Radio bind:group={role} value="contributor">
						{i18n.t('console.settings.users.roles.contributor')}
					</Radio>
					<span>{i18n.t('console.settings.users.roleDesc.contributor')}</span>
				</div>
			</div>
			<div class="signup-note">
				{i18n.t('console.settings.users.learnMore')}
				<Link href="/docs/users#roles" target="_blank">
					{i18n.t('console.settings.users.rolesLink')}
					{#snippet end()}
						<IconBoxArrowUpRight size={12} />
					{/snippet}
				</Link>
			</div>
		</SplitControl>
	{:else}
		<SplitControl label={i18n.t('console.common.name')}>
			<FormControl>
				<TextInput
					bind:value={guestName}
					state={guestNameError ? 'error' : undefined}
					bind:input={guestNameEl}
				/>
				{#if guestNameError}
					<Validation state="error">
						{guestNameError}
					</Validation>
				{/if}
			</FormControl>
		</SplitControl>
	{/if}
</Modal>

<style lang="scss">
	.signup-note {
		font-size: 14px;
		color: var(--text-light);
		margin-top: 10px;
	}

	.roles {
		display: flex;
		flex-direction: column;
		gap: 6px;
		div {
			display: flex;
			align-items: center;
			span {
				flex: 1;
				text-align: right;
				font-size: 12px;
				color: var(--text-light);
			}
		}
	}
</style>

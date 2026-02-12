import type { OrganizationRole } from '@hyvor/design/cloud';
import { authOrganizationStore } from './stores';
import { get } from 'svelte/store';

export function getOrganizationRole(): OrganizationRole {
	return get(authOrganizationStore).role;
}

export function canAccessBilling() {
	const role = getOrganizationRole();
	return role === 'admin' || role === 'billing';
}

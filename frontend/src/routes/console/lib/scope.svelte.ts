import { goto } from '$app/navigation';
import type { Scope } from './types';
import { consoleUrlWithBlog } from './consoleUrl';

let currentScopes = $state<Scope[]>([]);

export function setScopes(scopes: Scope[]) {
	currentScopes = scopes;
}

export function getScopes(): Scope[] {
	return currentScopes;
}

export function can(scope: Scope): boolean {
	return currentScopes.includes(scope);
}

export function cant(scope: Scope): boolean {
	return !can(scope);
}

export function redirectIfCant(scope: Scope) {
	if (cant(scope)) {
		goto(consoleUrlWithBlog('/'));
	}
}

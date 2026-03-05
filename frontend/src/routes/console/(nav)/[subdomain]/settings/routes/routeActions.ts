import consoleApi from '../../../../lib/consoleApi';
import type { Route } from '../../../../lib/types';

export function getRoutes() {
	return consoleApi.get<Route[]>({
		endpoint: '/routes'
	});
}

export function deleteRoute(id: number) {
	return consoleApi.delete({
		endpoint: `/route/${id}`
	});
}

interface CreateRouteData {
	name: string;
	match: string;
	template: string;
	posts_filter: string | null;
	content_type: string | null;
}

export function createRoute(route: CreateRouteData) {
	return consoleApi.post<Route>({
		endpoint: '/route',
		data: route
	});
}

export function updateRoute(id: number, route: Partial<Route>) {
	return consoleApi.patch<Route>({
		endpoint: `/route/${id}`,
		data: route
	});
}

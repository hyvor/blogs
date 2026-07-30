import consoleApi from '../../../../lib/consoleApi';

export interface HyvorPostIntegration {
	id: number;
	created_at: number;
	newsletter_id: number;
	embed_code: string;
	embed_default_code: string;
}

export type HyvorPostIntegrationData = { data: HyvorPostIntegration | null };

export function loadHyvorPost() {
	return consoleApi.get<HyvorPostIntegrationData>({
		endpoint: '/integrations/hyvor-post'
	});
}

export function connectHyvorPost() {
	return consoleApi.post<HyvorPostIntegration>({
		endpoint: '/integrations/hyvor-post/connect',
	});
}

export function disconnectHyvorPost() {
	return consoleApi.post({
		endpoint: '/integrations/hyvor-post/disconnect'
	});
}

export function updateHyvorPostEmbedCode(embed_code: string | null) {
	return consoleApi.patch<HyvorPostIntegration>({
		endpoint: '/integrations/hyvor-post',
		data: { embed_code }
	});
}

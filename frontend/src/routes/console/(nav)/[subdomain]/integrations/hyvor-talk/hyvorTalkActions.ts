import consoleApi from '../../../../lib/consoleApi';

export interface HyvorTalkIntegration {
	id: number;
	created_at: number;
	website_id: number;
	embed_code: string;
	embed_default_code: string;
}

export type HyvorTalkIntegrationData = { data: HyvorTalkIntegration | null };

export function loadHyvorTalk() {
	return consoleApi.get<HyvorTalkIntegrationData>({
		endpoint: '/integrations/hyvor-talk'
	});
}

export function connectHyvorTalk() {
	return consoleApi.post<HyvorTalkIntegration>({
		endpoint: '/integrations/hyvor-talk/connect'
	});
}

export function disconnectHyvorTalk() {
	return consoleApi.post({
		endpoint: '/integrations/hyvor-talk/disconnect'
	});
}

export function updateHyvorTalkEmbedCode(embed_code: string | null) {
	return consoleApi.patch<HyvorTalkIntegration>({
		endpoint: '/integrations/hyvor-talk',
		data: { embed_code }
	});
}

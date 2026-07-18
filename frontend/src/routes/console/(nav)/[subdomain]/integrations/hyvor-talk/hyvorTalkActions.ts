import consoleApi from '../../../../lib/consoleApi';

export interface HyvorTalkIntegration {
	id: number;
	created_at: number;
	website_id: number;
}

export type HyvorTalkIntegrationData<Connected extends boolean = boolean> = {
	connected: Connected;
	data: Connected extends true ? HyvorTalkIntegration : undefined;
};

export function loadHyvorTalk() {
	return consoleApi.get<HyvorTalkIntegrationData>({
		endpoint: '/integrations/hyvor-talk'
	});
}

export function createHyvorTalkIntegration() {
	return consoleApi.post<HyvorTalkIntegration>({
		endpoint: '/integrations/hyvor-talk'
	});
}

export function deleteHyvorTalkIntegration() {
	return consoleApi.delete({
		endpoint: '/integrations/hyvor-talk'
	});
}
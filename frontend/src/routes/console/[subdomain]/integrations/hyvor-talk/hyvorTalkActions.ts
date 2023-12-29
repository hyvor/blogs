import consoleApi from "../../../lib/consoleApi";

interface HyvorTalkIntegration {
    id: number,
    created_at: number,
    website_id: number,
}

export type HyvorTalkIntegrationData = {
    connected: false
} | {
    connected: true,
    data: HyvorTalkIntegration
}

export function loadHyvorTalk() {

    return consoleApi.get<HyvorTalkIntegrationData>({
        endpoint: '/integrations/hyvor-talk',
    })

}

export function createHyvorTalkIntegration() {
    return consoleApi.post<HyvorTalkIntegration>({
        endpoint: '/integrations/hyvor-talk',
    })
}
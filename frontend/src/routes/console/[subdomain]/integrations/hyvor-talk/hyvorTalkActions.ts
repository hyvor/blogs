import consoleApi from "../../../lib/consoleApi";
import type { HyvorTalkGatedContentRule } from "../../../lib/types";

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

export function deleteHyvorTalkIntegration() {
    return consoleApi.delete({
        endpoint: '/integrations/hyvor-talk',
    })
}


export function getGatedContentRules() {

    return consoleApi.get<HyvorTalkGatedContentRule[]>({
        endpoint: `/integrations/hyvor-talk/gated-content-rules`,
    })

}

export function getMembershipPlans() {

    return consoleApi.get<{
        currency: string,
        plans: {
            name: string;
            monthly_price: number;
        }[]
    }>({
        endpoint: `/integrations/hyvor-talk/membership-plans`,
    })

}
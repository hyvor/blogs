import consoleApi from "../../../../lib/consoleApi";
import type { HyvorTalkGatedContentRule } from "../../../../lib/types";

export interface HyvorTalkIntegration {
    id: number,
    created_at: number,
    website_id: number,
}

export type HyvorTalkIntegrationData<Connected extends boolean = boolean> = {
    connected: Connected,
    data: Connected extends true ? HyvorTalkIntegration : undefined
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

export function createGatedContentRule(tagId: number, planName: string, gate: string | null) {
    return consoleApi.post<HyvorTalkGatedContentRule>({
        endpoint: `/integrations/hyvor-talk/gated-content-rule`,
        data: {
            tag_id: tagId,
            minimum_plan: planName,
            gate,
        }
    })
}

export function updateGatedContentRule(ruleId: number, planName: string, gate: string | null) {
    return consoleApi.patch<HyvorTalkGatedContentRule>({
        endpoint: `/integrations/hyvor-talk/gated-content-rule/${ruleId}`,
        data: {
            minimum_plan: planName,
            gate,
        }
    })
}

export function deleteGatedContentRule(ruleId: number) {
    return consoleApi.delete({
        endpoint: `/integrations/hyvor-talk/gated-content-rule/${ruleId}`,
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
import consoleApi from "../../../../lib/consoleApi";
import type { HyvorTalkGatedContentRule } from "../../../../lib/types";


export function getGatedContentRules() {

    return consoleApi.get<HyvorTalkGatedContentRule[]>({
        endpoint: `/integrations/hyvor-talk/gated-content-rules`,
    })

}
import {useActions, useValues} from "kea";
import getSubdomain from "./subdomain";
import webhooksLogic from "../logic/webhooksLogic";

export function getWebhooksLogic() {
    return webhooksLogic({subdomain: getSubdomain()})
}

export function useWebhooksValues() {
    return useValues(getWebhooksLogic())
}
export function useWebhooksActions() {
    return useActions(getWebhooksLogic());
}
import consoleApi from "../../../../lib/consoleApi";
import type {CustomDomainHosting} from "../../../../lib/types";

export function getCustomDomainHosting() {
    return {
        status: "pending",
        // status: "active",
        certificate: null,
        valid_from: null,
        valid_to: null
    } as CustomDomainHosting
    // return consoleApi.get({
    //
    // });
}
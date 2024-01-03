import consoleApi from "../../lib/consoleApi";


export function forceCancelSubscription() {
    return consoleApi.delete({
        endpoint: '/billing/subscription'
    });
}
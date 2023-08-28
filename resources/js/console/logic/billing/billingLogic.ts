import {kea, key, props, path, actions, reducers, events} from "kea";

import type { billingLogicType } from "./billingLogicType";
import {Subscription, Usage} from "../../types";
import {ajax} from "kea-ajax";
import api from "../../lib/api";
import {actionToUrl} from "kea-router";

interface UsageTypes {
    users: Usage,
    media: Usage,
    auto_translate: Usage,
    gpt: Usage
}

interface ApiResponse {
    usage: UsageTypes,
    subscriptions: Subscription[]
}

const billingLogic = kea<billingLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),
    path(key => ['billing', key]),

    actions({
        setUsage: (usage: {users: Usage, media: Usage, auto_translate: Usage}) => ({usage}),
        setSubscriptions: (subscriptions: Subscription[]) => ({subscriptions}),

        navigateToBilling: () => false,
    }),

    actionToUrl(({ props }) => ({
        navigateToBilling: ({id}) => `/console/${props.subdomain}/billing`,
    })),

    ajax(({actions, props}) => ({

        load: async () => {
            const data = await api.get<ApiResponse>(props.subdomain, '/billing');
            actions.setUsage(data.usage);
            actions.setSubscriptions(data.subscriptions);
        },

        forceCancel: async ({onCancel} : {onCancel :Function}) => {
            await api.delete(props.subdomain, '/billing/subscription');
            onCancel();
        }

    })),

    reducers({
        usage: [
            {} as UsageTypes,
            {
                setUsage: (_, {usage}) => usage
            }
        ],
        subscriptions: [
            [] as Subscription[],
            {
                setSubscriptions: (_, {subscriptions}) => subscriptions
            }
        ]
    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

])

export default billingLogic;
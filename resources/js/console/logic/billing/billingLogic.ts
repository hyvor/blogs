import {kea, key, props, path, actions, reducers, events} from "kea";

import type { billingLogicType } from "./billingLogicType";
import {Subscription, Usage} from "../../types";
import {ajax} from "kea-ajax";
import api from "../../lib/api";

interface ApiResponse {
    usage: {
        users: Usage,
        media: Usage
    },
    subscriptions: Subscription[]
}

const billingLogic = kea<billingLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),
    path(key => ['billing', key]),

    actions({
        setUsage: (usage: {users: Usage, media: Usage}) => ({usage}),
        setSubscriptions: (subscriptions: Subscription[]) => ({subscriptions})
    }),

    ajax(({actions, props}) => ({

        load: async () => {
            const data = await api.get<ApiResponse>(props.subdomain, '/billing');
            actions.setUsage(data.usage);
            actions.setSubscriptions(data.subscriptions);
        }

    })),

    reducers({
        usage: [
            {} as {
                users: Usage,
                media: Usage
            },
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
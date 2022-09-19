import {actions, events, kea, key, path, props, reducers} from "kea";
import api from "../../lib/api";
import {ajax} from "kea-ajax";
import {
    PaddlePayment,
    PaddleSubscriptionInfo,
    SubscriptionFrequency,
    SubscriptionPlan,
} from "../../types";

import type { paddleLogicType } from "./paddleLogicType";

const paddleLogic = kea<paddleLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),
    path(key => ['subscription', key]),

    actions({
        setData: (data) => ({data}),
    }),

    ajax(({actions, props}) => ({

        load: async () => {
            const data = await api.get(props.subdomain, '/billing/paddle');
            actions.setData(data);
        },

        createSubscription: async (
            { onLoad, plan, frequency, referral } :
            { onLoad: (link: string) => void, plan: SubscriptionPlan, frequency: SubscriptionFrequency, referral: string }
        ) => {
            type Response = { link: string }
            const data = await api.post<Response>(props.subdomain, '/billing/paddle/subscription', {
                plan, frequency, referral
            });
            onLoad(data.link);
        },

        updateSubscription: async (
            { onSuccess, plan, frequency } :
            { onSuccess: Function, plan: SubscriptionPlan, frequency: SubscriptionFrequency }
        ) => {
            await api.patch(props.subdomain, '/billing/paddle/subscription', {
                plan, frequency
            })
            onSuccess();
        },

        cancelSubscription: async (
            { onSuccess } :
            { onSuccess: Function }
        ) => {
            await api.delete(props.subdomain, '/billing/paddle/subscription');
            onSuccess();
        }

    })),

    reducers({

        data: [
            {} as
            {
                info: PaddleSubscriptionInfo,
                payments: PaddlePayment[],
            },
            {
                setData: (_, {data}) => data
            }
        ],
    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

]);

export default paddleLogic;
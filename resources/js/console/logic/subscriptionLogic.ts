import {actions, events, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";
import {subscriptionLogicType} from "./subscriptionLogicType";
import {ajax} from "kea-ajax";
import {Receipt, Subscription, SubscriptionFrequency, SubscriptionInfo, SubscriptionPlan, Usage} from "../types";

const subscriptionLogic = kea<subscriptionLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),
    path(key => ['subscription', key]),

    actions({
        setData: (data) => ({data}),
    }),

    ajax(({actions, props}) => ({

        load: async () => {
            const data = await api.get(props.subdomain, '/billing');
            actions.setData(data);
        },

        createSubscription: async (
            { onLoad, plan, frequency } :
            { onLoad: (link: string) => void, plan: SubscriptionPlan, frequency: SubscriptionFrequency }
        ) => {
            type Response = { link: string }
            const data = await api.post<Response>(props.subdomain, '/billing/subscription', {
                plan, frequency
            });
            onLoad(data.link);
        },

        updateSubscription: async (
            { onSuccess, plan, frequency } :
            { onSuccess: Function, plan: SubscriptionPlan, frequency: SubscriptionFrequency }
        ) => {
            await api.patch(props.subdomain, '/billing/subscription', {
                plan, frequency
            })
            onSuccess();
        },

        cancelSubscription: async (
            { forced = false, onSuccess } :
            { forced?: boolean, onSuccess: Function }
        ) => {
            await api.delete(props.subdomain, '/billing/subscription', {forced});
            onSuccess();
        }

    })),

    reducers({

        data: [
            {} as
            {
                info: SubscriptionInfo,
                subscriptions: Subscription[],
                receipts: Receipt[],
                usage: {
                    users: Usage,
                    media: Usage
            }
        }, {
            setData: (_, {data}) => data
        }],
    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

]);

export default subscriptionLogic;
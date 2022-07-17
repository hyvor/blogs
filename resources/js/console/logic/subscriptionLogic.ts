import {actions, events, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";
import {subscriptionLogicType} from "./subscriptionLogicType";
import {ajax} from "kea-ajax";
import {Receipt, Subscription, SubscriptionInfo, Usage} from "../types";

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

        createSubscription: async ({ onLoad, plan, frequency, quantity }) => {
            type Response = { link: string }
            const data = await api.post<Response>(props.subdomain, '/billing/subscription', {
                plan, frequency, quantity
            });
            onLoad(data.link);
        },

        updateSubscription: async ({ onSuccess, plan, frequency, quantity }) => {
            await api.patch(props.subdomain, '/billing/subscription', {
                plan, frequency, quantity
            })
            onSuccess();
        },

        cancelSubscription: async ({ forced, onSuccess}) => {
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
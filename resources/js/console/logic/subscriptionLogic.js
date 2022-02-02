import { kea } from "kea";
import api from "../lib/api";


const subscriptionLogic = kea({

    key: props => props.subdomain,
    
    path: key => ['subscription', key],

    actions: {
        setData: (data) => ({data}),
    },

    ajax: ({actions, props}) => ({

        load: async () => {
            const data = await api.get(props.subdomain, '/subscription');
            actions.setData(data);
        },

        createSubscription: async ({ onLoad, plan, frequency, quantity }) => {
            const data = await api.post(props.subdomain, '/subscription', {
                plan, frequency, quantity
            });
            onLoad(data.payLink);
        },

        updateSubscription: async ({ onSuccess, plan, frequency, quantity }) => {
            await api.patch(props.subdomain, '/subscription', {
                plan, frequency, quantity
            })
            onSuccess();
        },

        cancelSubscription: async ({ forced, onSuccess}) => {
            await api.delete(props.subdomain, '/subscription', {forced});
            onSuccess();
        }

    }),

    reducers: {

        /**
         * output from GET /subscription endpoint
         * 
         * {
         *      receipts:
         *      info:
         *      subscriptions:
         *      usage:
         * }
         */
        data: [{}, {
            setData: (_, {data}) => data
        }],
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default subscriptionLogic;
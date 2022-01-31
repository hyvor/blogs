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

        createSubscription: async () => {

        },

        updateSubscription: async () => {

        },

        cancelSubscription: async () => {

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
        }]
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default subscriptionLogic;
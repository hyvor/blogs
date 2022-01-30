import { kea } from "kea";
import { update } from "lodash";
import api from "../lib/api";


const redirectsLogic = kea({

    key: props => props.subdomain,

    path: key => ['redirect', key],

    actions: {
        setRedirectList: (redirect) => ({redirect}),
        removeFromList: (id) => ({id}),
        addRedirect: (redirect) => ({redirect}),
        updateRedirect: (redirect) => ({redirect}),
    },

    ajax: ({actions, props}) => ({

        load: async ({offset = 0, type}) => {
            const redirect = await api.get(props.subdomain, '/redirect', {
                offset,
                limit: 10,
                type,
            });
            actions.setRedirectList(redirect);
        },

        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/redirect/${id}`);
        },

        create: async ({old_url, new_url, type}) => {
            const redirect = await api.post(props.subdomain, '/redirect', {
                    old_url: old_url,
                    new_url: new_url,
                    type: type 
                });
            actions.addRedirect(redirect);
        },

        update: async ({id}) => {
            // const redirect = await api.put(props.subdomain, `/redirect/${id}`, {
            //     old_url: old_url,
            //     new_url: new_url,
            //     type: type 
            // });
            // actions.updateRedirect(redirect);
            console.log('The id has come here now');
        }

    }),

    reducers: {

        redirect: [[], {
            setRedirectList: (_, {redirect}) => redirect,
            removeFromList: (state, {id}) => state.filter(m => m.id !== id),
            addRedirect: (state, {redirect}) => [redirect, ...state],
            updateRedirect: (state, {id}) => state.filter(m => m.id !== id)

        }]

    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default redirectsLogic;
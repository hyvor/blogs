import { kea } from "kea";
import api from "../lib/api";
import axios from 'axios';
import {toast} from 'react-toastify'




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
                })
                // .then(response => {
                //     if(response.status === 422){
                //         toast.error("There shouldn't be spaces in the match URL (Enter a - Instead).")
                //     }
                // });
            actions.addRedirect(redirect);
        },

        updateData: async ({userId, oldUrl, newUrl, type}) => {
            const redirect = await api.put(props.subdomain, `/redirect/${userId}`, {
                    old_url: oldUrl,
                    new_url: newUrl,
                    type: type 
                });
            actions.addRedirect(redirect);
        },
    }),

    reducers: {

        redirect: [[], {
            setRedirectList: (_, {redirect}) => redirect,
            removeFromList: (state, {id}) => state.filter(m => m.id !== id),
            addRedirect: (state, {redirect}) => [redirect, ...state],
            updateRedirect: (state, {redirect}) => [redirect, ...state],
        }]
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default redirectsLogic;
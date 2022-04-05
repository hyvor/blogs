import { kea } from "kea";
import api from "../lib/api";

const redirectsLogic = kea({

    key: props => props.subdomain,

    path: key => ['redirect', key],

    actions: {
        setRedirectList: (redirect) => ({redirect}),
        setRedirectListHasMore: (has) => ({has}),

        removeFromList: (id) => ({id}),
        addRedirect: (redirect) => ({redirect}),
        updateRedirect: (redirect) => ({redirect}),
    },

    ajax: ({ values, actions, props }) => ({ 

        load: async ({offset = 0, type}) => {
            // const redirect = await api.get(props.subdomain, '/redirect', {
            //     offset,
            //     limit: 20,
            //     type,
            // });
            const redirect = await api.get(props.subdomain, '/redirect');
            actions.setRedirectListHasMore(redirect.length === 50);
            actions.setRedirectList(redirect);
        },

        loadRedirectListMore: async ({offset}) => {
            // console.log(offset)
            const response = await api.get(props.subdomain, '/redirect', {
                offset
            });
            actions.setRedirectListHasMore(response.length === 50);
            actions.setRedirectList([...values.redirect, ...response])
        },

        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/redirect/${id}`);
        },

        create: async ({oldUrl, newUrl, type}) => {
            // console.log(oldUrl)
            // console.log(newUrl)
            // console.log(type)

            const redirect = await api.post(props.subdomain, '/redirect', {
                    path: oldUrl,
                    to: newUrl,
                    type: type 
                })
            actions.addRedirect(redirect);
        },

        updateData: async ({userId, oldUrl, newUrl, type}) => {
            const redirect = await api.put(props.subdomain, `/redirect/${userId}`, {
                    path: oldUrl,
                    to: newUrl,
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
            updateRedirect:(state, {redirect}) => state.map(
                stateRedirect => stateRedirect.id === redirect.id ? redirect : stateRedirect
            ),
        }],

        redirectList: [[], {
            setRedirectList: (_, {redirect}) => redirect
        }],

        redirectListHasMore: [false, {
            setRedirectListHasMore: (_, {has}) => has 
        }],

    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default redirectsLogic;
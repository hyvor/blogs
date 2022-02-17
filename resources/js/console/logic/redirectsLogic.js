import { kea } from "kea";
import api from "../lib/api";

const redirectsLogic = kea({

    key: props => props.subdomain,

    path: key => ['redirect', key],

    actions: {
        setRedirectList: (redirect) => ({redirect}),
        removeFromList: (id) => ({id}),
        addRedirect: (redirect) => ({redirect}),
        updateRedirect: (redirect) => ({redirect}),
        // setRedirectListHasMore: (has) => ({has}),
    },

    ajax: ({actions, props}) => ({ 

        load: async ({offset = 0, type}) => {
            const redirect = await api.get(props.subdomain, '/redirect', {
                offset,
                limit: 500,
                type,
            });
            actions.setRedirectList(redirect);
        },
        // loadRedirectListMore: async ({offset}) => {
        //     const response = await api.get(props.subdomain, '/redirect', {
        //         filters: values.filters,
        //         offset
        //     });
        //     // response.forEach(post => {
        //     //     const builtPostLogic = postLogic.build({id: post.id, data: post}, false);
        //     //     builtPostLogic.mount();
        //     // })
        //     actions.setRedirectListHasMore(response.length === 50);
        //     // actions.setRedirectList([...values.postsList, ...response.map(val => val.id)])
        // },

        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/redirect/${id}`);
        },

        create: async ({oldUrl, newUrl, type}) => {
            console.log(oldUrl)
            console.log(newUrl)
            console.log(type)

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
            // updateRedirect: (state, {redirect}) => [redirect, ...state],
            updateRedirect:(state, {redirect}) => state.map(
                stateRedirect => stateRedirect.id === redirect.id ? redirect : stateRedirect
            ),
            // redirectListHasMore: [false, {
            //     setRedirectListHasMore: (_, {has}) => has 
            // }],
        }]
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default redirectsLogic;
import {actions, events, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";

import type { redirectsLogicType } from "./redirectsLogicType";
import {ajax} from "kea-ajax";
import {Redirect} from "../types";

const redirectsLogic = kea<redirectsLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),

    path(key => ['redirect', key]),

    actions({
        setRedirectList: (redirects: Redirect[]) => ({redirects}),
        setRedirectListHasMore: (has: boolean) => ({has}),

        removeFromList: (id: number) => ({id}),
        addRedirect: (redirect: Redirect) => ({redirect}),
        updateRedirect: (redirect: Redirect) => ({redirect}),
    }),

    ajax(({ values, actions, props }) => ({

        load: async () => {
            const redirect = await api.get<Redirect[]>(props.subdomain, '/redirects');
            actions.setRedirectListHasMore(redirect.length === 25);
            actions.setRedirectList(redirect);
        },

        loadMore: async () => {
            const response = await api.get<Redirect[]>(props.subdomain, '/redirects', {
                offset: values.redirects.length
            });
            actions.setRedirectListHasMore(response.length === 25);
            actions.setRedirectList([...values.redirects, ...response])
        },

        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/redirect/${id}`);
        },

        create: async ({path, to, type, onCreate}) => {
            const redirect = await api.post<Redirect>(props.subdomain, '/redirect', {
                    path,
                    to,
                    type
                })
            actions.addRedirect(redirect);
            onCreate()
        },

        update: async ({id, path, to, type, onUpdate}) => {
            const redirect = await api.put<Redirect>(props.subdomain, `/redirect/${id}`, {
                path,
                to,
                type: type
            });
            actions.updateRedirect(redirect);
            onUpdate()
        },

    })),

    reducers({

        redirects: [
            [] as Redirect[],
            {
                setRedirectList: (_, {redirects}) => redirects,
                removeFromList: (state, {id}) => state.filter(m => m.id !== id),
                addRedirect: (state, {redirect}) => [redirect, ...state],
                updateRedirect:(state, {redirect}) => state.map(
                    stateRedirect => stateRedirect.id === redirect.id ? redirect : stateRedirect
                ),
            }
        ],

        hasMore: [false, {
            setRedirectListHasMore: (_, {has}) => has 
        }],

    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

]);

export default redirectsLogic;
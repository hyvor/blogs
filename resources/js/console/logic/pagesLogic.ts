import {actions, events, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";
import postLogic from "./postLogic";
import {pagesLogicType} from "./pagesLogicType";
import {actionToUrl} from "kea-router";
import {ajax} from "kea-ajax";
import {Post} from "../types";

const pagesLogic = kea<pagesLogicType>([


    props({} as {subdomain: string}),
    key(props => props.subdomain),
    path(key => ['pages', key]),

    actions({
        setPagesList: (list) => ({list}),
        navigateToPage: (id) => ({id}),
        navigateToPages: () => false
    }),

    actionToUrl(({ props }) => ({
        navigateToPage: ({id}) => `/console/${props.subdomain}/pages/${id}`,
        navigateToPages: () => `/console/${props.subdomain}/pages`
    })),

    ajax(({ values, props, actions }) => ({

        /**
         * First and more loading uses seperate actiosn because
         * we want seperate loading states
         */
        loadPagesList: async () => {
            const pages = await api.get<Post[]>(props.subdomain, '/pages');
            pages.forEach(post => {
                const builtPostLogic = postLogic.build({id: post.id, data: post});
                builtPostLogic.mount();
            })
            actions.setPagesList(pages.map(val => val.id))
        },

        createPage: async (setPageLoading) => {
            const response = await api.post<Post>(props.subdomain, '/post', {
                is_page: true
            });

            const builtPostLogic = postLogic.build({id: response.id, data: response});
            builtPostLogic.mount();

            actions.setPagesList([response.id, ...values.pagesList])
            actions.navigateToPage(response.id);
            setPageLoading(false);
        },

    })),

    reducers({
        pagesList: [[], {
            setPagesList: (_, {list}) => list
        }],
    }),

    events(({actions}) => ({
        afterMount: [
            actions.loadPagesList
        ]
    }))

])

export default pagesLogic
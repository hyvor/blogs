import { kea } from "kea";
import api from "../lib/api";
import userBlogsLogic from "./userBlogsLogic";
import postLogic from "./postLogic";

const pagesLogic = kea({

    key: props => props.subdomain,

    path: key => ['pages', key],

    actions: {
        setPagesList: (list) => ({list}),
        navigateToPage: (id) => ({id}),
        navigateToPages: () => false
    },

    actionToUrl: ({ props }) => ({
        navigateToPage: ({id}) => `/${props.subdomain}/page/${id}`,
        navigateToPages: () => `/${props.subdomain}/pages`
    }),

    ajax: ({ values, props, actions }) => ({

        /**
         * First and more loading uses seperate actiosn because
         * we want seperate loading states
         */
        loadPagesList: async () => {
            const pages = await api.get(props.subdomain, '/pages');
            pages.forEach(post => {
                const builtPostLogic = postLogic.build({id: post.id, data: post}, false);
                builtPostLogic.mount();
            })
            actions.setPagesList(pages.map(val => val.id))
        },

        createPage: async () => {
            const response = await api.post(props.subdomain, '/post');

            actions.getPostsLoadSuccess([response.id, ...values.postsList])
            actions.navigateToPage(response.id);
        },

    }),

    reducers: {
        pagesList: [[], {
            setPagesList: (_, {list}) => list
        }],
    },

    events: ({actions}) => ({
        afterMount: [
            actions.loadPagesList
        ]
    })

})

export default pagesLogic
import { kea } from "kea";
import api from "../lib/api";
import postLogic from "./postLogic";

const postsLogic = kea({

    key: props => props.subdomain,

    path: key => ['posts', key],

    actions: {
        changeFilter: (name, value) => ({name, value}),

        setPostsList: (list) => ({list}),
        setPostsListHasMore: (has) => ({has}),

        navigateToPost: (id) => ({id}),
        navigateToPosts: () => false
    },

    actionToUrl: ({ props }) => ({
        navigateToPost: ({id}) => `/${props.subdomain}/posts/${id}`,
        navigateToPosts: () => `/${props.subdomain}/posts`
    }),

    ajax: ({ values, props, actions }) => ({

        /**
         * First and more loading uses seperate actiosn because
         * we want seperate loading states
         */
        loadPostsList: async () => {
            const response = await api.get(props.subdomain, '/posts', {
                filters: values.filters
            });
            response.forEach(post => {
                const builtCounterLogic = postLogic.build({id: post.id, data: post}, false);
                builtCounterLogic.mount();
            })
            actions.setPostsListHasMore(response.length === 50);
            actions.setPostsList(response.map(val => val.id))
        },
        loadPostsListMore: async ({offset}) => {
            const response = await api.get(props.subdomain, '/posts', {
                filters: values.filters,
                offset
            });
            response.forEach(post => {
                const builtCounterLogic = postLogic.build({id: post.id, data: post}, false);
                builtCounterLogic.mount();
            })
            actions.setPostsListHasMore(response.length === 50);
            actions.setPostsList([...values.postsList, ...response.map(val => val.id)])
        },

        createPost: async () => {
            const response = await api.post(props.subdomain, '/post');

            actions.getPostsLoadSuccess([response.id, ...values.postsList])
            actions.navigateToPost(response.id);
        },        

        savePost: async ({ id, data }) => {
            const response = await api.patch(props.subdomain, `/post/${id}`, data);
            actions.setPosts(response); // update the post object
        }

    }),

    /* loadersWithHasMore: ({ values, props, actions }) => ({

        postsList: [[], {
            getPosts: async ({ offset }) => {
                const response = await api.get(props.subdomain, '/posts', {
                    filters: values.filters,
                    offset
                });
                actions.getPostsSetHasMore(response.length === 50);

                response.forEach(post => {
                    const builtCounterLogic = postLogic.build({id: post.id}, false);
                    builtCounterLogic.mount();
                    builtCounterLogic.actions.set(post);
                })

                return response.map(val => val.id); // just the IDs
            },
        }],
        
    }), */

    /* loaders: ({ values, props, actions }) => ({

        postsCounts: [{
            all: null,
            published: null,
            draft: null,
            scheduled: null,
            deleted: null,
            your: null
        }, {
            loadPostsCounts: async () => {
                //return await api.get(props.subdomain, '/posts-counts');
                return null;
            }
        }],

        tags: [[], {
            loadTags: () => {

            }
        }]

    }), */

    listeners: ({actions}) => ({
        changeFilter: () => actions.getPostsLoad()
    }),

    reducers: {

        postsList: [[], {
            setPostsList: (_, {list}) => list
        }],
        postsListHasMore: [false, {
            setPostsListHasMore: (_, {has}) => has 
        }],

        filters: [
            {
                status: 'all',
                author: 'all',
                tag: 'all',
                dateStart: null,
                dateEnd: null,
                search: ''
            },
            {
                changeFilter: (state, {name, value}) => ({...state, ...{[name]: value}})
            }
        ]
    },

    events: ({actions}) => ({
        afterMount: []
    })

})

export default postsLogic
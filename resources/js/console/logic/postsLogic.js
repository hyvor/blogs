import { kea } from "kea";
import api from "../lib/api";
import postLogic from "./postLogic";

const postsLogic = kea({

    key: props => props.subdomain,

    path: key => ['posts', key],

    actions: {
        changeFilter: (name, value) => ({name, value}),
        updatePost: (id, key, value) => ({id, key, value}),

        navigateToPost: (id) => ({id}),
        navigateToPosts: () => false
    },

    actionToUrl: ({ props }) => ({
        navigateToPost: ({id}) => `/${props.subdomain}/posts/${id}`,
        navigateToPosts: () => `/${props.subdomain}/posts`
    }),

    ajax: ({ values, props, actions }) => ({

        createPost: async () => {
            const response = await api.post(props.subdomain, '/post');

            actions.getPostsLoadSuccess([response.id, ...values.postsList])
            actions.navigateToPost(response.id);
        },

        deletePost: async ({id}) => {
            await api.delete(props.subdomain, `/post/${id}`);
            actions.getPostsLoadSuccess(values.postsList.filter(pId => pId !== id));
            actions.navigateToPosts();
        },

        savePost: async ({ id, data }) => {
            const response = await api.patch(props.subdomain, `/post/${id}`, data);
            actions.setPosts(response); // update the post object
        }

    }),

    loadersWithHasMore: ({ values, props, actions }) => ({

        /**
         * posts in the post-list preview
         * Just the IDs
         * Data is saved in postsStorage
         */
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
        
    }),

    loaders: ({ values, props, actions }) => ({

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

    }),

    listeners: ({actions}) => ({
        changeFilter: () => actions.getPostsLoad()
    }),

    reducers: {

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
        afterMount: [
            actions.loadPostsCounts
        ]
    })

})

export default postsLogic
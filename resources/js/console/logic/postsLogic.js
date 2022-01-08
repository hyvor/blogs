import { kea } from "kea";
import api from "../lib/api";

const postsLogic = kea({

    key: props => props.subdomain,

    path: key => ['posts', key],

    actions: {
        changeFilter: (name, value) => ({name, value}),
        setPosts: (posts) => ({posts}),

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

            actions.setPosts(response);
            actions.getPostsLoadSuccess([response.id, ...values.postsList])
            actions.navigateToPost(response.id);
        },

        deletePost: async ({id}) => {
            await api.delete(props.subdomain, `/post/${id}`);

            actions.getPostsLoadSuccess(values.postsList.filter(pId => pId !== id));
            actions.navigateToPosts();
        },

        updatePost: async ({ id, data }) => {
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
                actions.setPosts(response)
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

    selectors: {
        findById: [
            (s) => [s.posts],
            (posts) => {
                return id => posts[id]
            }
        ]
    },

    reducers: {

        posts: [{}, { // id => PostObject storage
            setPosts: (state, { posts }) => {
                const obj = {}
                if (!Array.isArray(posts)) posts = [posts]
                posts.forEach(val => obj[val.id] = val)
                return {...state, ...obj};
            }
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
        afterMount: [
            actions.loadPostsCounts
        ]
    })

})

export default postsLogic
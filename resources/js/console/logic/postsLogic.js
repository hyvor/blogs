import { kea } from "kea";
import api from "../lib/api";


const postsLogic = kea({

    key: props => props.subdomain,

    actions: {
        changeFilter: (name, value) => ({name, value}),
        setPosts: (posts) => ({posts})
    },

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
            }
        }],

        post: {
            getPost: async (id) => await api.get(props.post, '/post')
        },
        
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
                return await api.get(props.subdomain, '/posts-counts');
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

        posts: [{}, { // id => PostObject storage
            setPosts: (state, { posts }) => {
                const obj = {}
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
import { kea } from "kea";
import api from "../lib/api";


const postsLogic = kea({

    key: props => props.subdomain,

    actions: {
        changeFilter: (name, value) => ({name, value}),
        setPosts: (posts) => ({posts})
    },

    loaders: ({ values, props, actions }) => ({

        /**
         * posts in the post-list preview
         * Just the IDs
         * Data is saved in postsStorage
         */
        postsList: [[], {
            getPosts: async ({ page }) => {
                const response = await api.get(props.subdomain, '/posts', {
                    filters: values.filters,
                    page
                });
                actions.getPostsSetHasMore(response.hasMore)
                actions.setPosts(response.posts)
                return response.posts.map(val => val.id); // just the IDs
            }
        }],

        post: {
            getPost: async (id) => await api.get(props.post, '/post')
        },
        
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
                status: null,
                author: null,
                tag: null,
                dateStart: null,
                dateEnd: null,
                search: ''
            },
            {
                changeFilter: (state, {name, value}) => ({...state, ...{[name]: value}})
            }
        ]
    },

})

export default postsLogic
import { kea } from "kea";
import api from "../lib/api";
import blogsLogic from "./blogsLogic";
import postLogic from "./postLogic";

const postsLogic = kea({

    key: props => props.subdomain,

    path: key => ['posts', key],

    actions: {
        changeFilter: (name, value) => ({name, value}),

        setPostsList: (list) => ({list}),
        setPostsListHasMore: (has) => ({has}),

        setCounts: (counts) => ({counts}),

        navigateToPost: (id) => ({id}),
        navigateToPosts: () => false
    },

    actionToUrl: ({ props }) => ({
        navigateToPost: ({id}) => `/console/${props.subdomain}/posts/${id}`,
        navigateToPosts: () => `/console/${props.subdomain}/posts`
    }),

    ajax: ({ values, props, actions }) => ({

        getCounts: async () => {
            const counts = await api.get(props.subdomain, '/blog/post-counts');
            actions.setCounts(counts);
        },

        /**
         * First and more loading uses seperate actiosn because
         * we want seperate loading states
         */
        loadPostsList: async () => {
            const posts = await api.get(props.subdomain, '/posts', {
                filters: values.filters
            });
            posts.forEach(post => {
                const builtPostLogic = postLogic.build({id: post.id, data: post}, false);
                builtPostLogic.mount();
            })
            actions.setPostsListHasMore(posts.length === 50);
            actions.setPostsList(posts.map(val => val.id))
        },
        loadPostsListMore: async ({offset}) => {
            const response = await api.get(props.subdomain, '/posts', {
                filters: values.filters,
                offset
            });
            response.forEach(post => {
                const builtPostLogic = postLogic.build({id: post.id, data: post}, false);
                builtPostLogic.mount();
            })
            actions.setPostsListHasMore(response.length === 50);
            actions.setPostsList([...values.postsList, ...response.map(val => val.id)])
        },

        createPost: async () => {
            const response = await api.post(props.subdomain, '/post');

            actions.getPostsLoadSuccess([response.id, ...values.postsList])
            actions.navigateToPost(response.id);
        },

    }),

    listeners: ({actions}) => ({
        changeFilter: () => actions.loadPostsList()
    }),

    reducers: ({props}) => ({

        // object returned by /counts
        // { all: count, status: {[statuses+featured]: count}, authors/tags: [{id,name,count}],  }
        counts: [null, {
            setCounts: (_, {counts}) => counts
        }],

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
                language: blogsLogic.values.findBlogBySubdomain(props.subdomain).blog.defaultLanguage.id,
                dateStart: null,
                dateEnd: null,
                search: ''
            },
            {
                changeFilter: (state, {name, value}) => ({...state, ...{[name]: value}})
            }
        ]
    }),

    events: ({actions}) => ({
        afterMount: [
            actions.getCounts
        ]
    })

})

export default postsLogic
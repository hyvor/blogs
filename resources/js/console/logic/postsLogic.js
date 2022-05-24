import dayjs from "dayjs";
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

        /**
         * First and more loading uses seperate actiosn because
         * we want seperate loading states
         */
        loadPostsList: async () => {
            const filters = values.filters
            const posts = await api.get(props.subdomain, '/posts', getPostParamsFromFilters(filters));
            posts.forEach(post => {
                const builtPostLogic = postLogic.build({id: post.id, data: post}, false);
                builtPostLogic.mount();
            })
            actions.setPostsListHasMore(posts.length === 50);
            actions.setPostsList(posts.map(val => val.id))
        },
        loadPostsListMore: async ({offset}) => {
            const response = await api.get(props.subdomain, '/posts', 
                {...getPostParamsFromFilters(filters), offset}
            );
            response.forEach(post => {
                const builtPostLogic = postLogic.build({id: post.id, data: post}, false);
                builtPostLogic.mount();
            })
            actions.setPostsListHasMore(response.length === 50);
            actions.setPostsList([...values.postsList, ...response.map(val => val.id)])
        },

        createPost: async () => {
            const response = await api.post(props.subdomain, '/post');

            actions.setPostsList([response.id, ...values.postsList])
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
            // these are sent to the backend
            {
                status: 'all',
                author: 'all',
                tag: 'all',
                startDate: null,
                endDate: null,
                search: ''
            },
            {
                changeFilter: (state, {name, value}) => {
                    if (typeof name === 'object') {
                        return {...state, ...name}
                    } else {
                        return {...state, ...{[name]: value}}
                    }
                }
            }
        ]

    }),

})

export default postsLogic

function getPostParamsFromFilters(filters) {
    return {
        status: filters.status === 'all' ? null : filters.status,
        author_id: filters.author === 'all' ? null : filters.author,
        tag_id: filters.tag === 'all' ? null : filters.tag,
        start_timestamp: filters.startDate ? dayjs(filters.startDate).unix() : null,
        end_timestamp: filters.endDate ? dayjs(filters.endDate).unix() : null,
        search: null
    }
}
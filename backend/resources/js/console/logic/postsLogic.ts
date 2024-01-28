import dayjs from "dayjs";
import {actions, kea, key, listeners, path, props, reducers, selectors} from "kea";
import api from "../lib/api";
import postLogic from "./postLogic";

import type { postsLogicType } from "./postsLogicType";
import {Filters, Post, PostCounts} from "../types";
import {actionToUrl} from "kea-router";
import {ajax} from "kea-ajax";

const postsLogic = kea<postsLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),
    path(key => ['posts', key]),

    actions({
        changeFilter: (name, value) => ({name, value}),

        setPostsList: (list) => ({list}),
        setPostsListHasMore: (has) => ({has}),

        setCounts: (counts: PostCounts) => ({counts}),

        navigateToPost: (id) => ({id}),
        navigateToPosts: () => false,

        setActivePostId: (id: number | null) => ({id}),
        addLoadedPostIds: (ids: number[]) => ({ids}),
    }),

    actionToUrl(({ props }) => ({
        navigateToPost: ({id}) => `/console/${props.subdomain}/posts/${id}`,
        navigateToPosts: () => `/console/${props.subdomain}/posts`
    })),

    ajax(({ values, props, actions }) => ({

        loadPost: async({id}: {id: number}) => {
            const post = await api.get<Post>(props.subdomain, `/post/${id}`);
            const builtPostLogic = postLogic.build({id, data: post});
            builtPostLogic.mount();
            actions.addLoadedPostIds([id])
        },

        /**
         * First and more loading uses seperate actiosn because
         * we want seperate loading states
         */
        loadPostsList: async () => {
            const filters = values.filters
            const posts = await api.get<Post[]>(props.subdomain, '/posts', getPostParamsFromFilters(filters));
            posts.forEach(post => {
                const builtPostLogic = postLogic.build({id: post.id, data: post});
                builtPostLogic.mount();
            })

            actions.addLoadedPostIds(posts.map(val => val.id))
            actions.setPostsListHasMore(posts.length === 50);
            actions.setPostsList(posts.map(val => val.id))
        },

        loadPostsListMore: async ({offset}) => {
            const response = await api.get<Post[]>(props.subdomain, '/posts',
                {...getPostParamsFromFilters(values.filters), offset}
            );
            response.forEach(post => {
                const builtPostLogic = postLogic.build({id: post.id, data: post});
                builtPostLogic.mount();
            })
            actions.setPostsListHasMore(response.length === 50);
            actions.setPostsList([...values.postsList, ...response.map(val => val.id)])
        },

        createPost: async ({onLoad} : {onLoad: ()=> void}) => {
            const post = await api.post<Post>(props.subdomain, '/post');

            const builtPostLogic = postLogic.build({id: post.id, data: post});
            builtPostLogic.mount();

            actions.setPostsList([post.id, ...values.postsList])
            actions.navigateToPost(post.id);
            onLoad();
        },

    })),

    listeners(({actions}) => ({
        changeFilter: () => actions.loadPostsList()
    })),

    reducers(({props}) => ({

        loadedPostIds: [
            [] as number[],
            {
                addLoadedPostIds: (state, {ids}) => [...state, ...ids]
            }
        ],

        activePostId: [
            null as null | number,
            {
                setActivePostId: (_, {id}) => id,
            }
        ],

        counts: [null as null | PostCounts, {
            setCounts: (_, {counts}) => counts
        }],

        postsList: [
            [] as number[],
            {
                setPostsList: (_, {list}) => list
            }
        ],

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
            } as Filters,
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

    })),

    selectors(() => ({

        hasPostLoaded: [
            (s) => [s.loadedPostIds],
            (loadedPostIds) => (id: number) => loadedPostIds.includes(id)
        ]

    }))

])

export default postsLogic

function getPostParamsFromFilters(filters: Filters) {
    return {
        status: filters.status === 'all' ? null : filters.status,
        author_id: filters.author === 'all' ? null : filters.author,
        tag_id: filters.tag === 'all' ? null : filters.tag,
        start_timestamp: filters.startDate ? dayjs(filters.startDate).unix() : null,
        end_timestamp: filters.endDate ? dayjs(filters.endDate).unix() : null,
        search: filters.search
    } as any
}
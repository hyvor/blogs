/**
 * Logic for a single post
 */

import { kea } from "kea";
import api from "../lib/api";
import postsLogic from "./postsLogic";
import subdomainLogic from "./subdomainLogic";

const postLogic = kea({

    key: props => props.id,

    path: key => ['post', key],

    actions: {

        set: (obj) => ({obj}),
        updatePostValue: (key, val) => ({obj: {[key]:val}})

    },

    ajax: ({actions, props}) => ({
 
        loadPost: async () => {
            const response = await api.get(subdomainLogic.values.subdomain, `/post/${props.id}`);
            actions.set(response);
        },

        savePost: async () => {
            const response = await api.patch(subdomainLogic.values.subdomain, )
        }

    }),

    reducers: ({actions, props, selectors}) => ({

        // post's current state in the front-end
        post: [{}, { 
            set: (_, {obj}) => obj,
            updatePostValue: (state, {obj}) => ({...state, ...obj})
        }],

        // the really saved post in the back-end
        postOriginal: [{}, {
            set: (_, {obj}) => obj
        }]

    }),

    events: ({actions, values, props}) => ({
        afterMount: () =>  {
            return;
            if (Object.keys(values.post).length) // post already loaded
                return;

            /**
             * Check if the post was already loaded in the posts array
             * Otherwise, load internally
             */
            const subdomain = subdomainLogic.values.subdomain;
            const post = postsLogic({subdomain}).values.posts[props.id]
            if (post) {
                actions.set(post);
                actions.loadPostSuccess()
            } else {
                actions.loadPost();
            }
        }
    })

})

export default postLogic;
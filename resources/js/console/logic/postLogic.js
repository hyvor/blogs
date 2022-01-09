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
        setOriginal: (obj) => ({obj}),
        updatePostValue: (key, val) => ({obj: {[key]:val}})

    },

    ajax: ({actions, values,  props}) => ({
 
        loadPost: async () => {
            const response = await api.get(subdomainLogic.values.subdomain, `/post/${props.id}`);
            actions.set(response);
        },

        deletePost: async () => {
            await api.delete(props.subdomain, `/post/${props.id}`);
            actions.getPostsLoadSuccess(values.postsList.filter(pId => pId !== id));
            actions.navigateToPosts();
        },

        savePost: async () => {
            const diff = getPostDiff(values.post, values.postOriginal);
            
            if (Object.keys(diff).length === 0) {
                return false;
            }

            const response = await api.patch(subdomainLogic.values.subdomain, `/post/${props.id}`, diff)
            actions.setOriginal(response);
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
            set: (_, {obj}) => obj,
            setOriginal: (_, {obj}) => obj
        }]

    }),

    events: ({actions, values, props}) => ({
        afterMount: () =>  {
            if (props.data) {
                actions.set(props.data);
            } else {
                actions.loadPost();
            }
        }
    })

})

function getPostDiff(post, postOriginal) {

    const updatableKeys = [
        'published_at',
        'status',
        'is_featured',
        'slug',
        'content',
        'title',
        'description',
        'featured_image',
        'canonical_url',
        'code_head',
        'code_foot'
    ];

    const diff = {};

    for (var i in postOriginal) {
        if (updatableKeys.indexOf(i) !== -1 && postOriginal[i] !== post[i]) {
            diff[i] = post[i];
        }
    }

    return diff;

}

export default postLogic;
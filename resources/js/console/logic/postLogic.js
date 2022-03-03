/**
 * Logic for a single post
 */

import { kea } from "kea";
import slugify from "../../helpers/slugify";
import api from "../lib/api";
import postsLogic from "./postsLogic";
import subdomainLogic from "./subdomainLogic";

const postLogic = kea({

    key: props => props.id,

    path: key => ['post', key],

    actions: {

        set: (obj) => ({obj}),
        setOriginal: (obj) => ({obj}),
        updatePostValue: (key, value) => ({key, value})

    },

    ajax: ({actions, selectors, props}) => ({
 
        loadPost: async () => {
            const response = await api.get(subdomainLogic.values.subdomain, `/post/${props.id}`);
            actions.set(response);
        },

        deletePost: async () => {
            // remove from posts list
            const subdomain = subdomainLogic.values.subdomain
            const postsLogicInst = postsLogic({subdomain});
            postsLogicInst.actions.setPostsList(postsLogicInst.values.postsList.filter(pId => pId !== props.id));
            postsLogicInst.actions.navigateToPosts();
            await api.delete(subdomain, `/post/${props.id}`);
        },

        /**
         * Usually, you can update post values and call savePost
         * However, if you want to savePost without updating values first (ex: when publishing)
         * use update object
         */
        savePost: async ({onSave, update = {}}) => {
            const diff = {...selectors.getDiff(), ...update};
            
            if (Object.keys(diff).length === 0) {
                return false;
            }

            const response = await api.patch(subdomainLogic.values.subdomain, `/post/${props.id}`, diff)
            actions.setOriginal(response);

            /**
             * Really update front-end data
             */
            for (var i in update) {
                actions.updatePostValue(i, update[i]);
            }


            typeof onSave === 'function' && onSave(response);
        }

    }),

    listeners: ({actions, values}) => ({

        updatePostValue: ({key, value}) => {

            // auto update slug when updating title 
            // if the original value is null
            if (key === 'title' && values.postOriginal.slug === null) {
                actions.updatePostValue("slug", slugify(value))
            }

        }

    }),

    selectors: {

        // diff of orignal and state
        getDiff: [
            (selectors) => [selectors.post, selectors.postOriginal],
            (post, postOriginal) => {
                return getPostDiff(post, postOriginal)
            }
        ]

    },  

    reducers: ({actions, props, selectors}) => ({

        // post's current state in the front-end
        post: [{}, { 
            set: (_, {obj}) => obj,
            updatePostValue: (state, {key, value}) => ({...state, ...{[key]: value}})
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

    // keys are defined to drop authors and tags
    const updatableKeys = [
        'published_at',
        'status',
        'is_featured',
        'slug',
        'content',
        'content_unsaved',
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
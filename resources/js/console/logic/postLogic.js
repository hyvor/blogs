/**
 * Logic for a single post
 */

import { kea } from "kea";
import slugify from "../../helpers/slugify";
import api from "../lib/api";
import postsLogic from "./postsLogic";
import subdomainLogic from "./subdomainLogic";
import { diff } from 'deep-object-diff';
import merge from 'deepmerge'

async function updatePost(postId, diff) {

    if (diff.variants) {
        for (let languageId in diff.variants) {
            await api.patch(
                subdomainLogic.values.subdomain, `/post/${postId}/variant`,
                {...diff.variants[languageId], ...{language_id: languageId}}
            )
        }
        delete diff.variants;
    }

    return await api.patch(subdomainLogic.values.subdomain, `/post/${postId}`, diff)

}

const postLogic = kea({

    key: props => props.id,

    path: key => ['post', key],

    actions: {

        set: (obj) => ({obj}),
        setOriginal: (obj) => ({obj}),
        updatePostValue: (key, value) => ({key, value}),
        updatePostVariantValue: (key, value, languageId) => ({key, value, languageId}),
        addVariant: (variant) => ({variant}),

    },

    ajax: ({actions, selectors, props}) => ({
 
        loadPost: async () => {
            const response = await api.get(subdomainLogic.values.subdomain, `/post/${props.id}`);
            console.log(response)
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
         * Used for auto saving
         */
        savePost: async () => {
            const diff = selectors.getDiff()
            
            if (Object.keys(diff).length === 0) {
                return false;
            }

            const response = await updatePost(props.id, diff);
            // const response = await api.patch(subdomainLogic.values.subdomain, `/post/${props.id}`, diff)
            actions.setOriginal(response);
        },

        /**
         * Used for forced saving/publishing/unpublishing (usually on button click)
         */
        forceSavePost: async ({onSave, update}) => {

            const diff = {...selectors.getDiff(), ...update};

            const response = await updatePost(props.id, diff);
            actions.set(response)

            typeof onSave === 'function' && onSave(response);
        },

        createVariant: async ({languageId, onCreate}) => {

            const variant = await api.post(subdomainLogic.values.subdomain,
                `/post/${props.id}/variant`,
                {
                    language_id: languageId
                }
            );

            actions.addVariant(variant);

            onCreate && onCreate();

        },

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
                return diff(postOriginal, post)
            }
        ]

    },  

    reducers: ({actions, props, selectors}) => ({

        // post's current state in the front-end
        post: [null, {
            set: (_, {obj}) => obj,
            updatePostValue: (state, {key, value}) => ({...state, ...{[key]: value}}),
            updatePostVariantValue: (state, {key, value, languageId}) => {
                const obj = {
                    variants: {
                        [languageId]: {
                            [key]: value
                        }
                    }
                }
                return merge(state, obj);
            },
            addVariant: (state, {variant}) => {
                return merge(state, {
                    variants: {
                        [variant.language_id]: variant
                    }
                })
            }
        }],

        // the really saved post in the back-end
        postOriginal: [null, {
            set: (_, {obj}) => obj,
            setOriginal: (_, {obj}) => obj,
            addVariant: (state, {variant}) => {
                return merge(state, {
                    variants: {
                        [variant.language_id]: variant
                    }
                })
            }
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

export default postLogic;

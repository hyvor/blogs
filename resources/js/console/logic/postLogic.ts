/**
 * Logic for a single post
 */

import {actions, events, kea, key, listeners, path, props, reducers, selectors} from "kea";
import slugify from "../../helpers/slugify";
import api from "../lib/api";
import postsLogic from "./postsLogic";
import subdomainLogic from "./subdomainLogic";
import { diff } from 'deep-object-diff';
import merge from 'deepmerge'
import {postLogicType} from "./postLogicType";
import {Post} from "../types";
import {ajax} from "kea-ajax";

async function updatePost(post: Post, diff: Partial<Post>) {

    if (diff.variants) {
        for (let languageId in diff.variants) {
            await api.patch(
                subdomainLogic.values.subdomain, `/post/${post.id}/variant`,
                {...diff.variants[languageId], ...{language_id: languageId}}
            )
        }
        delete diff.variants;
    }

    if (diff.authors) {
        await api.patch(subdomainLogic.values.subdomain, `/post/${post.id}/authors`, {
            ids: post.authors.map(author => author.id)
        });
        delete diff.authors
    }
    if (diff.tags) {
        await api.patch(subdomainLogic.values.subdomain, `/post/${post.id}/tags`, {
            ids: post.tags.map(author => author.id)
        });
        delete diff.tags
    }

    return await api.patch<Post>(subdomainLogic.values.subdomain, `/post/${post.id}`, diff)

}

const postLogic = kea<postLogicType>([

    props({} as {id: number, data?: Post}),
    key(props => props.id),
    path(key => ['post', key]),

    actions({
        set: (obj) => ({obj}),
        setOriginal: (obj) => ({obj}),
        updatePostValue: (key, value) => ({key, value}),
        updatePostVariantValue: (key, value, languageId) => ({key, value, languageId}),
        addVariant: (variant) => ({variant}),
    }),

    ajax(({actions, selectors, props, values}) => ({
 
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
            const diff = values.diff
            
            if (Object.keys(diff).length === 0) {
                return false;
            }

            const response = await updatePost(values.post, diff);
            // const response = await api.patch(subdomainLogic.values.subdomain, `/post/${props.id}`, diff)
            actions.setOriginal(response);
        },

        /**
         * Used for forced saving/publishing/unpublishing (usually on button click)
         */
        forceSavePost: async ({onSave, update}) => {

            const diff = {...values.diff, ...update};

            const response = await updatePost(values.post, diff);
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

    })),

    listeners(({actions, values}) => ({

        updatePostValue: ({key, value}) => {

            // auto update slug when updating title 
            // if the original value is null
            if (key === 'title' && values.postOriginal.slug === null) {
                actions.updatePostValue("slug", slugify(value))
            }

        }

    })),

    selectors({

        // diff of orignal and state
        diff: [
            (selectors) => [selectors.post, selectors.postOriginal],
            (post, postOriginal) => {
                const d = diff(postOriginal, post) as Partial<Post>
                if (d.preview_id) delete d.preview_id;
                return d;
            }
        ]

    }),

    reducers(({actions, props, selectors}) => ({

        // post's current state in the front-end
        post: [
            null as Post | null,
            {
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
                    return merge(state, obj) as Post;
                },
                addVariant: (state, {variant}) => {
                    return merge(state, {
                        variants: {
                            [variant.language_id]: variant
                        }
                    }) as Post
                }
            }
        ],

        // the really saved post in the back-end
        postOriginal: [
            null as Post | null,
            {
                set: (_, {obj}) => obj,
                setOriginal: (_, {obj}) => obj,
                addVariant: (state, {variant}) => {
                    return merge(state, {
                        variants: {
                            [variant.language_id]: variant
                        }
                    }) as Post
                }
            }
        ]

    })),

    events(({actions, values, props}) => ({
        afterMount: () =>  {
            if (props.data) {
                actions.set(props.data);
            } else {
                actions.loadPost();
            }
        }
    }))

])

export default postLogic;

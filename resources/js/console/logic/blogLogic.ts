import {actions, kea, key, path, props, reducers, selectors} from "kea";
import {ajax} from 'kea-ajax';
import api from "../lib/api";
import {Blog, BlogVariant, Language, PostCounts, Tag, User} from "../types";
import usersLogic from "./usersLogic";
import tagsLogic from "./tagsLogic";
import languagesLogic from "./languagesLogic";

import type { blogLogicType } from "./blogLogicType";
import merge from "deepmerge";

interface BlogResponse {
    blog: Blog,
    counts: PostCounts,
    users: Array<User>,
    tags: Array<Tag>,
    languages: Array<Language>
}

// @ts-ignore
const blogLogic = kea<blogLogicType>([

    props({} as {subdomain: string}),
    key((props) => props.subdomain),
    path((key) => ['blog', key]),

    actions(({values}) => ({
        setBlog: (blog: Blog) => ({blog}),
        setOriginal: (blog: Blog) => ({blog}),
        updateBlogValue: (key: keyof Blog, value: any) => ({key, value}),
        updateBlogVariantValue: (key: keyof BlogVariant, value: any, languageId: number) => ({key, value, languageId}),
        discardChanges: (keys: Array<keyof Blog>) => ({keys, original: values.blogOriginal}),
        /*setOriginal: (blog) => ({blog}),
        updateBlogData: (key, value) => ({key, value}),
        discardChanges: (keys) => ({keys, original: values.blogOriginal}),*/
    })),

    ajax(({actions, selectors, props}) => ({

        load: async () => {
            const data : BlogResponse = await api.get(props.subdomain, '/blog');

            const usersLogicInst = usersLogic.build({subdomain: props.subdomain})
            usersLogicInst.mount();
            usersLogicInst.actions.addUsers(data.users);

            const tagsLogicInst = tagsLogic.build({subdomain: props.subdomain})
            tagsLogicInst.mount();
            tagsLogicInst.actions.addTags(data.tags);

            const languagesLogicInst = languagesLogic.build({subdomain: props.subdomain})
            languagesLogicInst.mount();
            languagesLogicInst.actions.setLanguages(data.languages);

            actions.setBlog(data.blog);
        },
        /*createVariant: async ({languageId}) => {
            // console.log(languageId)
            const blog = await api.post(props.subdomain, '/blog/variant', {
                languageId: languageId,
            })
            actions.addBlogVariant(blog);
        },*/
        
        save: async ({keys}) => {
            const diff = selectors.getDiff(keys);
            const blog = await api.patch<Blog>(props.subdomain, '/blog', diff);
            actions.setOriginal(blog);
        },

    })),

    reducers({

        blogOriginal: [
            null as Blog | null,
            {
                setBlog: (_, {blog}) => blog,
                setOriginal: (_, {blog}) => blog
            }
        ],

        blog: [
            null as Blog | null,
            {
                setBlog: (_, {blog}) => blog,
                updateBlogValue: (state, {key, value}) => (
                    {...state, ...{[key]: value === '' ? null : value}}
                ),
                updateBlogVariantValue: (state, {key, value, languageId}) => {
                    const obj = {
                        variants: {
                            [languageId]: {
                                [key]: value
                            }
                        }
                    }
                    return merge(state, obj) as Blog;
                },
                discardChanges: (blog, {original, keys}) => {
                    const obj : Partial<Blog> = {}
                    keys.forEach((key: keyof Blog) => {
                        // @ts-ignore
                        obj[key] = original[key]
                    })
                    return {...blog, ...obj};
                }
            }
        ]
    }),

    selectors({

        // diff of orignal and state
        getDiff: [
            (selectors) => [selectors.blog, selectors.blogOriginal],
            (blog: Blog, blogOriginal: Blog) => {
                return (keys: Array<keyof Blog>) => {
                    const diff = {} as Record<keyof Blog, any>;
                    let i: keyof Blog;
                    for (i in blogOriginal) {
                        if (keys.indexOf(i) >= 0 && blog[i] !== blogOriginal[i]) {
                            diff[i] = blog[i]
                        }
                    }
                    return Object.keys(diff).length > 0 ? diff : null;
                }
            }
        ]

    })

]);

export default blogLogic;

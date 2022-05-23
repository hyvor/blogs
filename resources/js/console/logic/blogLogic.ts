import {actions, kea, key, listeners, path, props, reducers, selectors} from "kea";
import {ajax} from 'kea-ajax';
import api from "../lib/api";
import {Blog, BlogVariant, Language, PostCounts, Tag, User} from "../types";
import usersLogic from "./usersLogic";
import tagsLogic from "./tagsLogic";
import languagesLogic from "./languagesLogic";

import type { blogLogicType } from "./blogLogicType";
import merge from "deepmerge";
import subdomainLogic from "./subdomainLogic";

interface BlogResponse {
    blog: Blog,
    counts: PostCounts,
    users: Array<User>,
    tags: Array<Tag>,
    languages: Array<Language>
}

const blogLogic = kea<blogLogicType>([

    props({} as {subdomain: string}),
    key((props) => props.subdomain),
    path((key) => ['blog', key]),

    actions(({values}) => ({
        setBlog: (blog: Blog) => ({blog}),
        setOriginal: (blog: Blog) => ({blog}),
        addBlogVariant: (variant: BlogVariant) => ({variant}),
        updateBlogValue: (key: keyof Blog, value: any) => ({key, value}),
        updateBlogVariantValue: (key: keyof BlogVariant, value: any, languageId: number) => ({key, value, languageId}),
        discardChanges: (keys: Array<keyof Blog>) => ({keys, original: values.blogOriginal}),
    })),

    ajax(({actions, values, props}) => ({

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

        createVariant: async ({languageId, onCreate} : {languageId: number, onCreate: Function}) => {
            const variant = await api.post<BlogVariant>(props.subdomain, '/blog/variant', {
                language_id: languageId,
            })
            actions.addBlogVariant(variant);
            onCreate(variant);
        },

        updateVariant: async ({data, languageId}) => {
            await api.patch(subdomainLogic.values.subdomain, `/blog/variant`, {...data, ...{language_id: languageId}})
        },

        updateBlog: async ({keys, variantKeys}) => {

            try {
                // update variants
                if (variantKeys) {
                    const variantDiff = values.getVariantDiff(variantKeys);

                    for (let languageId in variantDiff) {
                        const data = variantDiff[languageId]
                        await actions.updateVariant({data, languageId})
                    }
                }

                const diff = values.getDiff(keys);
                const blog = await api.patch<Blog>(props.subdomain, '/blog', diff);
                actions.setOriginal(blog);

            } catch (e) {
                console.error(e)
            }

        },

    })),

    reducers({

        blogOriginal: [
            null as Blog | null,
            {
                setBlog: (_, {blog}) => blog,
                setOriginal: (_, {blog}) => blog,
                addBlogVariant: (state, {variant}) => {
                    const obj = {
                        variants: {
                            [variant.language_id]: variant
                        }
                    }
                    return merge(state, obj) as Blog;
                },
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
                addBlogVariant: (state, {variant}) => {
                    const obj = {
                        variants: {
                            [variant.language_id]: variant
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

        // diff of original and state
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
        ],

        getVariantDiff: [
            (selectors) => [selectors.blog, selectors.blogOriginal],
            (blog: Blog, blogOriginal: Blog) => {

                const variants = blog.variants;
                const variantsOriginal = blogOriginal.variants;

                return (keys: Array<keyof BlogVariant>) => {
                    const diff = {} as Record<number, Partial<Record<keyof BlogVariant, any>>>
                    for (let x in variantsOriginal) {
                        let y : keyof BlogVariant;
                        for (y in variantsOriginal[x]) {
                            if (keys.indexOf(y) >= 0 && variants[x][y] !== variantsOriginal[x][y]) {
                                if (!diff[x]) {
                                    diff[x] = {}
                                }
                                diff[x][y] = variants[x][y]
                            }
                        }
                    }
                    return Object.keys(diff).length ? diff : null;
                }

            }
        ],

        subdomain: [
            (selectors) => [selectors.blog],
            (blog: Blog) => blog.subdomain
        ]

    }),

    /*listeners(({selectors}) => ({
        discardChanges: function() {
            console.log(selectors.subdomain())
           /!* const diff = selectors.getVariantDiff(['name']);
            console.log(diff)*!/
        }
    }))*/

]);

export default blogLogic;

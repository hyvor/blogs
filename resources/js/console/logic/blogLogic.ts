import {actions, kea, key, path, props, reducers, selectors} from "kea";
import {ajax} from 'kea-ajax';
import api from "../lib/api";
import {Blog, BlogVariant, Language, PostCounts, Tag, User} from "../types";
import usersLogic from "./usersLogic";
import tagsLogic from "./tagsLogic";
import languagesLogic from "./languagesLogic";

import type { blogLogicType } from "./blogLogicType";
import merge from "deepmerge";
import getSubdomain from "../logic-helpers/subdomain";
import postsLogic from "./postsLogic";

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

        load: async ({onLoad} : {onLoad: Function}) => {
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

            const postsLogicInst = postsLogic.build({subdomain: props.subdomain})
            postsLogicInst.mount();
            postsLogicInst.actions.setCounts(data.counts);

            actions.setBlog(data.blog);

            onLoad();
        },

        createVariant: async ({languageId, onCreate} : {languageId: number, onCreate: Function}) => {
            const variant = await api.post<BlogVariant>(props.subdomain, '/blog/variant', {
                language_id: languageId,
            })
            actions.addBlogVariant(variant);
            onCreate(variant);
        },

        updateVariant: async ({data, languageId}) => {
            await api.patch(getSubdomain(), `/blog/variant`, {...data, ...{language_id: languageId}})
        },

        updateBlogSave: async ({data, onUpdate}) => {
            const blog = await api.patch<Blog>(getSubdomain(), `/blog`, data);
            actions.setOriginal(blog);
            actions.setBlog(blog);
            onUpdate && onUpdate(blog);
        },

        updateBlog: async ({keys, variantKeys, onUpdate}) => {

            // update variants
            if (variantKeys) {
                const variantDiff = values.getVariantDiff(variantKeys);

                for (let languageId in variantDiff) {
                    const data = variantDiff[languageId as unknown as keyof typeof variantDiff]
                    await actions.updateVariant({data, languageId})
                }
            }

            const diff = values.getDiff(keys);

            if (diff) {
                const blog = await api.patch<Blog>(props.subdomain, '/blog', diff);
                actions.setOriginal(blog);
            }

            onUpdate && onUpdate();
        },

        deleteBlog: async({onDelete} : {onDelete: Function}) => {
            await api.delete(props.subdomain, '/blog');
            onDelete();
        },

    })),

    reducers({

        blogOriginal: [
            {} as Blog,
            {
                setBlog: (_, {blog}) => blog,
                setOriginal: (_, {blog}) => blog,
                addBlogVariant: (state, {variant}) => {
                    const copy = {...state};
                    copy.variants.push(variant)
                    return copy;
                },
            }
        ],

        blog: [
            {} as Blog,
            {
                setBlog: (_, {blog}) => blog,
                updateBlogValue: (state, {key, value}) => (
                    {...state, ...{[key]: value === '' ? null : value}} as Blog
                ),
                updateBlogVariantValue: (state, {key, value, languageId}) => {
                    const copy = {...state}
                    copy.variants = copy.variants.map(
                        variant => variant.language_id === languageId ?
                            {...variant, [key]: value || null} :
                            variant
                    );
                    return copy;
                },
                addBlogVariant: (state, {variant}) => {
                    const copy = {...state}
                    copy.variants.push(variant);
                    return copy;
                },
                discardChanges: (blog, {original, keys}) => {
                    const obj : Partial<Blog> = {}
                    keys.forEach((key: keyof Blog) => {
                        // @ts-ignore
                        obj[key] = original[key]
                    })
                    return {...blog, ...obj} as Blog;
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

                    for (let variantOriginal of variantsOriginal) {

                        const newVariant = variants.find(v => v.language_id === variantOriginal.language_id)

                        if (!newVariant)
                            continue;

                        let key : keyof BlogVariant;
                        for (key in variantOriginal) {
                            if (keys.indexOf(key) >= 0 && newVariant[key] !== variantOriginal[key]) {

                                if (!diff[variantOriginal.language_id]) {
                                    diff[variantOriginal.language_id] = {};
                                }

                                diff[variantOriginal.language_id][key] = newVariant[key];
                            }
                        }

                    }

                    /*for (let x in variantsOriginal) {
                        let y : keyof BlogVariant;
                        for (y in variantsOriginal[x]) {
                            if (keys.indexOf(y) >= 0 && variants[x][y] !== variantsOriginal[x][y]) {
                                if (!diff[x]) {
                                    diff[x] = {}
                                }
                                diff[x][y] = variants[x][y]
                            }
                        }
                    }*/

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

import {actions, kea, key, path} from "kea";
import {ajax} from 'kea-ajax';
import api from "../lib/api";
import {Blog, Language, PostCounts, Tag, User} from "../types";
import usersLogic from "./usersLogic";
import tagsLogic from "./tagsLogic";
import languagesLogic from "./languagesLogic";

import type { blogLogicType } from "./blogLogicType";

interface BlogResponse {
    blog: Blog,
    counts: PostCounts,
    users: Array<User>,
    tags: Array<Tag>,
    languages: Array<Language>
}

const blogLogic = kea<blogLogicType>([

    key((props: {subdomain: string}) => props.subdomain),
    path((key) => [key, 'blog']),

    actions(({values}) => ({
        setBlog: (blog: Blog) => ({blog}),
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
        }
        /*createVariant: async ({languageId}) => {
            // console.log(languageId)
            const blog = await api.post(props.subdomain, '/blog/variant', {
                languageId: languageId,
            })
            actions.addBlogVariant(blog);
        },
        
        save: async ({keys}) => {
            const diff = selectors.getDiff()(keys);
            const blog = await api.patch(props.subdomain, '/blog', diff);
            actions.setOriginal(blog);
        },*/

    }))

   // reducers: {



        /*blogOriginal: [{}, {
            setBlog: (_, {blog}) => blog,
            setOriginal: (_, {blog}) => blog
        }],

        blog: [{}, {
            setBlog: (_, {blog}) => blog,
            updateBlogData: (state, {key, value}) => ({...state, ...{[key]: value === '' ? null : value}}),
            discardChanges: (blog, {original, keys}) => {
                const obj = {}
                keys.forEach(key => {
                    obj[key] = original[key]
                })
                return {...blog, ...obj};
            } 
        }],*/

    // },

    /*selectors: {

        // diff of orignal and state
        getDiff: [
            (selectors) => [selectors.blog, selectors.blogOriginal],
            (blog, blogOriginal) => {
                return (keys) => {
                    const diff = {};
                    for (var i in blogOriginal) {
                        if (keys.indexOf(i) >= 0 && blog[i] !== blogOriginal[i]) {
                            diff[i] = blog[i]
                        }
                    }
                    return Object.keys(diff).length > 0 ? diff : null;
                }
            }
        ]

    },*/

]);

export default blogLogic;

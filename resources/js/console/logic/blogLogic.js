import { kea } from "kea";
import api from "../lib/api";

const blogLogic = kea({

    key: props => props.subdomain,

    path: key => ['blog', key],

    actions: ({values}) => ({
        setBlog: (blog) => ({blog}),
        setOriginal: (blog) => ({blog}),
        updateBlogData: (key, value) => ({key, value}),
        discardChanges: (keys) => ({keys, original: values.blogOriginal}),
    }),

    ajax: ({actions, selectors, props}) => ({

        load: async () => {
            const blog = await api.get(props.subdomain, '/blog');
            actions.setBlog(blog);
        },
        createVariant: async ({languageId}) => {
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
        },

    }),

    reducers: {

        blogOriginal: [{}, {
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
        }],

        featureImage: [[], {
            addFeatureImage: (state, {featureImage}) => [featureImage, ...state]
        }],

        icon: [[], {
            addIcon: (state, {icon}) => [icon, ...state]
        }],

    },

    selectors: {

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

    },

});

export default blogLogic;

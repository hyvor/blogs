import { kea } from "kea";
import api from "../lib/api";

const blogLogic = kea({

    key: props => props.subdomain,

    path: key => ['blog', key],

    actions: ({values}) => ({
        setBlog: (blog) => ({blog}),
        updateBlogData: (key, value) => ({key, value}),
        save: () => false,
        setToOriginal: () => ({original: values.blogOriginal}),
    }),

    ajax: ({actions, props}) => ({

        load: async () => {
            const blog = await api.get(props.subdomain, '/blog');
            actions.setBlog(blog);
        },

    }),

    reducers: {

        blogOriginal: [{}, {
            setBlog: (_, {blog}) => blog,
        }],

        blog: [{}, {
            setBlog: (_, {blog}) => blog,
            updateBlogData: (state, {key, value}) => ({...state, ...{[key]: value}}),
            setToOriginal: (_, {original}) => original 
        }]

    },

    selectors: {

        // diff of orignal and state
        getDiff: [
            (selectors) => [selectors.blog, selectors.blogOriginal],
            (blog, blogOriginal) => {
                const diff = {};
                for (var i in blogOriginal) {
                    if (blog[i] !== blogOriginal[i]) {
                        diff[i] = blog[i]
                    }
                }
                return Object.keys(diff).length > 0 ? diff : null;
            }
        ]

    },

});

export default blogLogic;
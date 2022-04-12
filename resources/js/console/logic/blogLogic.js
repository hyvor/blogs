import { kea } from "kea";
import api from "../lib/api";

const blogLogic = kea({

    key: props => props.subdomain,

    path: key => ['blog', key],

    actions: ({values}) => ({
        setBlog: (blog) => ({blog}),
        addBlogVarian: (user) => ({user}),
        updateBlog: (user) => ({user}),
        updateBlogData: (key, value) => ({key, value}),
        save: () => false,
        setToOriginal: () => ({original: values.blogOriginal}),
    }),

    ajax: ({actions, props}) => ({

        load: async () => {
            const blog = await api.get(props.subdomain, '/blog');
            actions.setBlog(blog);
        },
        createVariant: async ({languageId}) => {
            console.log(languageId)
            const blog = await api.post(props.subdomain, '/blog/variant', {
                languageId: languageId,
            })
            actions.addBlogVarian(blog);
        },
            
        updateData: async ({
            subdomainEdit, name, description, icon, featureImageId, social_facebook, social_twitter, 
            social_linkedin, social_youtube, social_instagram, social_github
            }) => {

            console.log( subdomainEdit, name, description, icon, featureImageId, social_facebook, social_twitter, 
                social_linkedin, social_youtube, social_instagram, social_github )

            const blog = await api.patch(props.subdomain, '/blog', {
                subdomain:subdomainEdit,
                name: name,
                description:description,
                icon:icon,
                featureImageId:featureImageId,
                social_facebook:social_facebook,
                social_twitter:social_twitter,
                social_linkedin:social_linkedin,
                social_youtube: social_youtube,
                social_instagram:social_instagram,
                social_github:social_github,
            });
            actions.updateBlogData(blog);
        },

    }),

    reducers: {

        blogOriginal: [{}, {
            setBlog: (_, {blog}) => blog,
        }],

        blog: [{}, {
            setBlog: (_, {blog}) => blog,
            addBlogVarian: (state, {blog}) => [blog, ...state],
            // updateBlog:(state, {user}) => state.map(
            //     stateUser => stateUser.id === user.id ? user : stateUser
            // ),
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
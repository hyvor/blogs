import axios from "axios";
import { kea } from "kea";
import { getUserEndpoint } from "../lib/api";
import subdomainLogic from "./subdomainLogic";

const blogsLogic = kea({
    actions: {
        addBlog: (userBlog) => ({userBlog}),
        setBlogs: (blogs) => ({blogs}),
    },
    ajax: ({actions, values}) => ({
        createBlog: async ({name, subdomain}) => {

            const res = await axios.post(
                getUserEndpoint('/blog'),
                {name, subdomain}
            )

            const userBlog = res.data

            actions.addBlog(userBlog)
            subdomainLogic.actions.setSubdomain(userBlog.blog.subdomain, null, true);

        },
        saveBlogsSort: async () => {

            const ids = values.blogs.map(b => b.blog.id);
            await axios.patch(getUserEndpoint('/blogs/sort'), {blog_ids: ids})

        }
    }),
    reducers: {
        blogs: [
            window.appConfig.blogs,
            {
                addBlog: (state, {userBlog}) => [...state, userBlog],
                setBlogs: (_, {blogs}) => blogs
            }
        ]
    },
    selectors: {
        findBlogBySubdomain: [
            (s) => [s.blogs],
            (blogs) => {
                return sub => blogs.find(b => b.blog.subdomain === sub)
            }
        ]
    }
})

export default blogsLogic;
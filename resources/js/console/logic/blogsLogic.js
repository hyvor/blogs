import { kea } from "kea";

const blogsLogic = kea({
    actions: {

    },
    reducers: {
        blogs: [
            window.appConfig.blogs
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
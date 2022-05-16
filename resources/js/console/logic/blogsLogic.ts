import axios, {AxiosResponse} from "axios";
import { kea } from "kea";
import { getUserEndpoint } from "../lib/api";
import subdomainLogic from "./subdomainLogic";

import { UserBlog } from "../objects/userblog";
import type { blogsLogicType } from "./blogsLogicType";
import {appConfig} from "../helpers";

const blogsLogic = kea<blogsLogicType>({

    actions: {
        addBlog: (userBlog: UserBlog) => ({ userBlog }),
        setBlogs: (blogs: Array<UserBlog>) => ({ blogs }),
    },

    ajax: ({ actions, values } : blogsLogicType) => ({
        createBlog: async (
            { name, subdomain, isDev} :
            { name: string, subdomain: string, isDev?: boolean}) => {

            const res: AxiosResponse<UserBlog> =  await axios.post(
                getUserEndpoint('/blog'),
                {name, subdomain, isDev}
            );
            const userBlog = res.data;
            actions.addBlog(userBlog);
            subdomainLogic.actions.setSubdomain(userBlog.blog.subdomain, null, true);
        },
        saveBlogsSort: async () => {
            const ids = values.blogs.map(b => b.blog.id);
            await axios.patch(getUserEndpoint('/blogs/sort'), { blog_ids: ids });
        }
    }),

    reducers: {
        blogs: [
            appConfig().blogs,
            {
                addBlog: (state, { userBlog }) => [...state, userBlog],
                setBlogs: (_, { blogs }) => blogs
            }
        ]
    },
    selectors: {
        findBlogBySubdomain: [
            (s) => [s.blogs],
            (blogs) => {
                return sub => blogs.find(b => b.blog.subdomain === sub);
            }
        ]
    }

});
export default blogsLogic;

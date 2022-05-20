import axios, {AxiosResponse} from "axios";
import {kea, MakeLogicType} from "kea";
import { getUserEndpoint } from "../lib/api";
import subdomainLogic from "./subdomainLogic";

import { UserBlog } from "../objects/userblog";
import {appConfig} from "../helpers";

interface Values {
    blogs: Array<UserBlog>,
    findBlogBySubdomain: (subdomain: string) => UserBlog,
    createBlogAjax: any
}

interface CreateBlogProps {
    name: string,
    subdomain: string,
    isDev?: boolean
}

interface Actions {
    addBlog: (userBlog: UserBlog) => {userBlog: UserBlog},
    setBlogs: (blogs: Array<UserBlog>) => {blogs: Array<UserBlog>}
    createBlog: (props: CreateBlogProps) => {}
    saveBlogSort: () => {}
}

type blogsLogicType = MakeLogicType<Values, Actions>

const blogsLogic = kea<blogsLogicType>({

    actions: {
        addBlog: (userBlog) => ({ userBlog }),
        setBlogs: (blogs) => ({ blogs }),
    },

    ajax: ({ actions, values } : blogsLogicType) => ({
        createBlog: async ({ name, subdomain, isDev} : CreateBlogProps) => {

            const data : any = {name};

            if (isDev) {
                data.is_dev = isDev;
            } else {
                data.subdomain = subdomain;
            }

            const res: AxiosResponse<UserBlog> =  await axios.post(getUserEndpoint('/blog'), data);
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
            (blogs: Array<UserBlog>) => {
                return (sub : string) => blogs.find(b => b.blog.subdomain === sub);
            }
        ]
    }

});
export default blogsLogic;

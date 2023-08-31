import axios, {AxiosResponse} from "axios";
import {actions, events, kea, reducers, selectors} from "kea";
import { getUserEndpoint } from "../lib/api";
import subdomainLogic from "./subdomainLogic";

import { UserBlog } from "../objects/userblog";
import {appConfig} from "../helpers";

import type { userBlogsLogicType} from "./userBlogsLogicType";
import {ajax} from "kea-ajax";

const userBlogsLogic = kea<userBlogsLogicType>([

    actions({
        addBlog: (userBlog) => ({ userBlog }),
        setBlogs: (blogs) => ({ blogs }),
    }),

    ajax(({ actions, values }) => ({
        createBlog: async (
            { name, subdomain, isDev, onCreate} :
            {
                name: string,
                subdomain: string,
                isDev?: boolean,
                onCreate?: (userBlog: UserBlog) => void
            }
        ) => {

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

            onCreate && onCreate(userBlog);

        },
        saveBlogsSort: async () => {
            const ids = values.blogs.map(b => b.blog.id);
            await axios.patch(getUserEndpoint('/blogs/sort'), { blog_ids: ids });
        }

    })),

    reducers({
        blogs: [
            [],
            {
                addBlog: (state, { userBlog }) => [...state, userBlog],
                setBlogs: (_, { blogs }) => blogs
            }
        ]
    }),

    selectors({
        findBlogBySubdomain: [
            (s) => [s.blogs],
            (blogs: Array<UserBlog>) => {
                return (sub : string) : UserBlog => blogs.find(b => b.blog.subdomain === sub) as UserBlog;
            }
        ]
    }),

    events(({actions}) => {
        return {
            afterMount: () => {
                actions.setBlogs(appConfig().blogs);
            }
        }
    })

]);
export default userBlogsLogic;

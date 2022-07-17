import {actions, events, kea, listeners, path, reducers} from "kea";
import { router } from "kea-router";
import blogLogic from "./blogLogic";
import userBlogsLogic from "./userBlogsLogic";
import postsLogic from "./postsLogic";
import sceneLogic from './sceneLogic';


import type { subdomainLogicType } from "./subdomainLogicType";
import {ConsoleWindow} from "../types";


const subdomainLogic = kea<subdomainLogicType>([

    path(['subdomain']),

    actions(({ values }) => ({
        setSubdomain:
            (subdomain: string, oldDomain: string | null, changeRoute: boolean = false) =>
            ({ old: oldDomain, subdomain, changeRoute })
    })),

    listeners(() => ({
        setSubdomain: ({ old, subdomain, changeRoute }) => {
            if (changeRoute) {
                var path = old ? location.pathname.replace('/console/' + old, '') : '';
                router.actions.push("/console/" + subdomain + path);
            }

            // pre-load blog settings
            const blogLogicInst = blogLogic({subdomain})
            blogLogicInst.mount()
            blogLogicInst.actions.load();

            // pre-load posts
            const postsLogicInst = postsLogic({subdomain})
            postsLogicInst.mount();
            postsLogicInst.actions.loadPostsList();

            /**
             * This is set because there are some places that
             * we cannot access kea data. Ex: inside prosemirror nodeviews (image uploading)
             * In that case, this is the only way to access the current subdomains
             * Don't use this in the main app
             */
            (window as ConsoleWindow).currentSubdomain = subdomain;
        }
    })),

    reducers(() => ({
        subdomain: [
            null as string | null,
            {
                setSubdomain: (_, { subdomain }) => subdomain
            }
        ]
    })),

    events(({ actions }) => ({
        afterMount: () => {
            var subdomain = findDefaultActiveSubdomain()
            if (subdomain)
                actions.setSubdomain(subdomain, subdomain)
            else {
                router.actions.push("/console/new");
            }
        }
    }))

])


function findDefaultActiveSubdomain() {

    const { subdomain: subdomainOnLoad} = sceneLogic.values.params;
    const { blogs, findBlogBySubdomain } = userBlogsLogic.values;

    if (subdomainOnLoad && findBlogBySubdomain(subdomainOnLoad)) {
        return subdomainOnLoad
    }
    
    return blogs.length ? blogs[0].blog.subdomain : null;
}

export default subdomainLogic;

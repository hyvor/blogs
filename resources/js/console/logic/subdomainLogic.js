import { kea, useValues } from "kea";
import { router } from "kea-router";
import blogLogic from "./blogLogic";
import blogsLogic from "./blogsLogic";
import postsLogic from "./postsLogic";
import sceneLogic from './sceneLogic';


const subdomainLogic = kea({

    path: ['subdomain'],

    actions: ({ values }) => ({
        setSubdomain: (subdomain, oldDomain, changeRoute) => ({ old: oldDomain, subdomain, changeRoute })
    }),

    listeners: () => ({
        setSubdomain: ({ old, subdomain, changeRoute }) => {
            if (changeRoute) {
                var path = old ? location.pathname.replace('/console/' + old, '') : '';
                router.actions.push("/console/" + subdomain + path);
            }
            
            // pre-load posts
            postsLogic({subdomain}).actions.loadPostsList();
            // pre-load blog settings
            blogLogic({subdomain}).actions.load();
        }
    }),

    reducers: () => ({
        subdomain: [null, {
            setSubdomain: (_, { subdomain }) => subdomain
        }]
    }),

    events: ({ actions }) => ({
        afterMount: () => {
            var subdomain = findDefaultActiveSubdomain()
            if (subdomain)
                actions.setSubdomain(subdomain, subdomain)
            else {
                router.actions.push("/console/new");
            }
        }
    })

})


function findDefaultActiveSubdomain() {

    const { subdomain: subdomainOnLoad} = sceneLogic.values.params;
    const { blogs, findBlogBySubdomain } = blogsLogic.values;

    if (subdomainOnLoad && findBlogBySubdomain(subdomainOnLoad)) {
        return subdomainOnLoad
    }
    
    return blogs.length ? blogs[0].blog.subdomain : null;
}

export default subdomainLogic;
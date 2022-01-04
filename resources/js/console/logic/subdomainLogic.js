import { kea, useValues } from "kea";
import { router } from "kea-router";
import blogsLogic from "./blogsLogic";
import postsLogic from "./postsLogic";
import sceneLogic from './sceneLogic';


const subdomainLogic = kea({

    actions: ({ values }) => ({
        setSubdomain: (subdomain, oldDomain) => ({ old: oldDomain || values.subdomain, subdomain })
    }),

    listeners: () => ({
        setSubdomain: ({ old, subdomain }) => {
            var path = location.pathname.replace('/console/' + old, '');
            router.actions.push("/console/" + subdomain + path);

            postsLogic({subdomain}).actions.getPostsLoad();
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
            actions.setSubdomain(subdomain, subdomain)
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
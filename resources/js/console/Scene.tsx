import { useValues } from 'kea'
import Billing from './Billing/Billing'
import BlogPreview from './BlogPreview/BlogPreview'
import userBlogsLogic from './logic/userBlogsLogic'
import sceneLogic from './logic/sceneLogic'
import Left from './Left/Left'
import NewBlog from './NewBlog'
import Pages from './Posts/Pages'
import Posts from './Posts/Posts'
import Settings from './Settings/Settings'
import Theme from './Theme/Theme'
import Welcome from "./Welcome/Welcome"
import React, {ReactNode} from 'react'
import blogLogic from "./logic/blogLogic"
import Loader from "./ReusableComponents/Loader"
import subdomainLogic from "./logic/subdomainLogic"
import BlogBlocked from "./Views/BlogBlocked";
import {hasTrialEndedAndNotActivated} from "./lib/blog-helpers";
import BlogTrialEnded from "./Views/BlogTrialEnded";

export const scenes = {
    error404: () => <div>404</div>,
    blogPreview: () => <BlogPreview />,
    posts: ({ postId } : { postId?: number }) => <Posts postId={postId} />,
    pages: ({ postId } : { postId?: number }) => <Pages postId={postId} />,
    settings: ({type} : {type?: string}) => <Settings type={type} />,
    theme: ({type} : {type?: string }) => <Theme />,
    billing: () => <Billing />,
    new: ({type} : {type? : string}) => <NewBlog type={type} />,
    welcome: () => <Welcome />
}

export default function Scene() {

    userBlogsLogic.mount()

    const { scene, params } = useValues(sceneLogic)

    const SceneComponent = scenes[scene as keyof typeof scenes] || scenes.error404

    return <div>
        <Left />
        <Middle scene={scene}><SceneComponent {...params} /></Middle>
    </div>

}

function Middle({ children, scene } : {children: ReactNode, scene: string}) {

    // load blog data

    const subdomain = subdomainLogic.values.subdomain

    if (subdomain === null) {
        return <div id="middle">{ children }</div>
    }

    const { loadAjax, blog } = useValues(blogLogic({subdomain}))

    function isBlogSceneBlocked() {
        return blog.is_blocked &&
            (
                scene === 'blogPreview' ||
                scene === 'posts' ||
                scene === 'pages' ||
                scene === 'theme'
            );
    }

    function isBlogTrialEndedAndNotActivated() {
        return hasTrialEndedAndNotActivated(blog.subdomain) &&
            scene !== 'billing' &&
            scene !== 'welcome' &&
            scene !== 'new';
    }

    return <div id="middle">
        {
            loadAjax.status === 'loading' ?
                <div className="posts-not-ready box">
                    <Loader size={40} />
                </div>
            :

                (
                     isBlogSceneBlocked() ?
                        <BlogBlocked /> :
                         (
                             isBlogTrialEndedAndNotActivated() ?
                                 <BlogTrialEnded /> :
                                 children
                         )
                )
        }
    </div>

}
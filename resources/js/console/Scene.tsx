import { useValues } from 'kea'
import subdomainLogic from './logic/subdomainLogic'
import Loader from './ReusableComponents/Loader';
import languagesLogic from "./logic/languagesLogic";

import Billing from './Billing/Billing'
import BlogPreview from './BlogPreview/BlogPreview'
import blogsLogic from './logic/blogsLogic'
import sceneLogic from './logic/sceneLogic'
import Left from './Left/Left'
import NewBlog from './NewBlog'
import Pages from './Posts/Pages'
import Posts from './Posts/Posts'
import Settings from './Settings/Settings'
import Theme from './Theme/Theme'
import Welcome from "./Welcome/Welcome"
import Comments from "./Comment/Comment"
import React from 'react'

export const scenes = {
    error404: () => <div>404</div>,
    blogPreview: () => <BlogPreview />,
    posts: ({ postId } : { postId?: number }) => <Posts postId={postId} />,
    pages: ({ postId } : { postId?: number }) => <Pages postId={postId} />,
    Comments: () => <Comments />,
    settings: ({type} : {type?: string}) => <Settings type={type} />,
    theme: ({type} : {type?: string }) => <Theme type={type} />,
    billing: () => <Billing />,
    new: ({type} : {type? : string}) => <NewBlog type={type} />,
    welcome: () => <Welcome />
}

export default function Scene() {

    blogsLogic.mount()

    const { scene, params } = useValues(sceneLogic)

    const SceneComponent = scenes[scene as keyof typeof scenes] || scenes.error404

    const { subdomain } = useValues(subdomainLogic)
    const { loadAjax: languageLoadAjax } = useValues(languagesLogic({subdomain}))

    return <div>
        <Left />
        {
            languageLoadAjax.status === 'loading' ?
            <div className="load-language">
                <Loader size={75} />
                </div>
            :
            <div id="middle"><SceneComponent {...params} /></div>
        }
    </div>

}
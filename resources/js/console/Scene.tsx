import { useValues } from 'kea'
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
import React, {ReactNode} from 'react'
import blogLogic from "./logic/blogLogic"
import Loader from "./ReusableComponents/Loader"
import subdomainLogic from "./logic/subdomainLogic"
import Comments from "./Comment/Comments"

export const scenes = {
    error404: () => <div>404</div>,
    blogPreview: () => <BlogPreview />,
    posts: ({ postId } : { postId?: number }) => <Posts postId={postId} />,
    pages: ({ postId } : { postId?: number }) => <Pages postId={postId} />,
    comments: () => <Comments />,
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

    return <div>
        <Left />
        <Middle><SceneComponent {...params} /></Middle>
    </div>

}

function Middle({ children } : {children: ReactNode}) {

    // load blog data
    const { loadAjax } = useValues(blogLogic({subdomain: subdomainLogic.values.subdomain}))

    return <div id="middle">{
        loadAjax.status === 'loading' ?
        <div className="posts-not-ready box">
            <Loader size={40} />
        </div> :
        children
    }</div>

}
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
import React from 'react'

export const scenes = {
    error404: () => <div>404</div>,
    blogPreview: () => <BlogPreview />,
    posts: ({ postId } : { postId?: number }) => <Posts postId={postId} />,
    pages: ({ postId } : { postId?: number }) => <Pages postId={postId} />,
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
        <div id="middle"><SceneComponent {...params} /></div>
    </div>

}
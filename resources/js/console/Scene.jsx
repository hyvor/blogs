import { useMountedLogic, useValues } from 'kea'
import BlogPreview from './BlogPreview/BlogPreview'
import blogsLogic from './logic/blogsLogic'
import sceneLogic from './logic/sceneLogic'
import Nav from './Nav/Nav'
import Posts from './Posts/Posts'
import Settings from './Settings/Settings'

export const scenes = {
    error404: () => <div>404</div>,
    blogPreview: () => <BlogPreview />,
    posts: ({ postId }) => <Posts postId={postId} />,
    settings: ({type}) => <Settings type={type} />,
}


export default function Scene() {

    blogsLogic.mount()

    const { scene, params } = useValues(sceneLogic)

    const SceneComponent = scenes[scene] || scenes.error404

    return <div>
        <Nav />
        <div id="middle"><SceneComponent {...params} /></div>
    </div>

}


function Theme() {
    return <div style={{height: "100%"}} className="box"></div>
}
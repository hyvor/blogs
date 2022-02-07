import { useValues } from 'kea'
import Billing from './Billing/Billing'
import BlogPreview from './BlogPreview/BlogPreview'
import blogsLogic from './logic/blogsLogic'
import sceneLogic from './logic/sceneLogic'
import Nav from './Nav/Nav'
import NewBlog from './NewBlog'
import Posts from './Posts/Posts'
import Settings from './Settings/Settings'
import Theme from './Theme/Theme'

export const scenes = {
    error404: () => <div>404</div>,
    blogPreview: () => <BlogPreview />,
    posts: ({ postId }) => <Posts postId={postId} />,
    settings: ({type}) => <Settings type={type} />,
    theme: () => <Theme />,
    billing: () => <Billing />,
    new: () => <NewBlog />,
}

const noNavScenes = ['new'];


export default function Scene() {

    blogsLogic.mount()

    const { scene, params } = useValues(sceneLogic)

    const SceneComponent = scenes[scene] || scenes.error404

    const noNav = noNavScenes.indexOf(scene) !== -1;

    return <div>
        { noNav ? null : <Nav /> }
        <div id="middle"><SceneComponent {...params} /></div>
    </div>

}
import { kea } from "kea";
import api from "../lib/api";



const postsLogic = kea({

    key: props => props.subdomain,

    loaders: ({ values, props }) => ({

        posts: [[], {
            loadPosts: async () => await api.get(props.subdomain, '/posts')
        }],
        
        
        post: {
            loadPost: async (id) => await api.get(props.post, '/post')
        }

    })

})

export default postsLogic
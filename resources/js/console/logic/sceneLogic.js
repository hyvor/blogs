import { kea } from "kea"

const routes = {
    '/console': 'dashboard',
    '/console/new': 'new',
    '/console/:subdomain/posts(/:postId)': 'posts',
    '/console/:subdomain/pages(/:postId)': 'pages',
    '/console/:subdomain': 'blogPreview',
    '/console/:subdomain/billing': 'billing',
    '/console/:subdomain/settings(/:type)': 'settings',
    '/console/:subdomain/theme': 'theme'
}

const sceneLogic = kea({
    actions: {
        setScene: (scene, params) => ({ scene, params }),
    },
    reducers: {
        scene: [
            null,
            {
                setScene: (_, payload) => payload.scene,
            },
        ],
        params: [
            {},
            {
                setScene: (_, payload) => payload.params || {},
            },
        ],
    },
    urlToAction: ({ actions, values }) => {
        return Object.fromEntries(
            Object.entries(routes).map(([path, scene]) => {
                return [path, (params) => actions.setScene(scene, params)]
            })
        )
    },
})

export default sceneLogic;
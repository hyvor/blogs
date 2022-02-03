import { kea } from "kea"

const routes = {
    '/': 'dashboard',
    '/:subdomain/posts(/:postId)': 'posts',
    '/:subdomain/pages(/:page)': 'posts',
    '/:subdomain': 'blogPreview',
    '/:subdomain/billing': 'billing',
    '/:subdomain/settings(/:type)': 'settings',
    '/:subdomain/theme': 'theme'
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
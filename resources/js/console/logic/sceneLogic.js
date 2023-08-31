import { kea } from "kea";

const routes = {
    '/console': 'welcome',
    '/console/new(/:type)': 'new',
    '/console/:subdomain/posts(/:postId)': 'posts',
    '/console/:subdomain/pages(/:postId)': 'pages',
    '/console/:subdomain/comments': 'comments',
    '/console/:subdomain': 'blogPreview',
    '/console/:subdomain/billing': 'billing',
    '/console/:subdomain/settings(/:type)': 'settings',
    '/console/:subdomain/tools(/:type)': 'tools',
    '/console/:subdomain/integrations(/:type)': 'integrations',
    '/console/:subdomain/theme(/:type)': 'theme'
};

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
        return Object.fromEntries(Object.entries(routes).map(([path, scene]) => {
            return [path, (params) => actions.setScene(scene, params)];
        }));
    },

});
export default sceneLogic;

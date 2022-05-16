import { kea } from "kea";
import type { sceneLogicType } from "./sceneLogicType";
const routes = {
    '/console': 'welcome',
    '/console/new(/:type)': 'new',
    '/console/:subdomain/posts(/:postId)': 'posts',
    '/console/:subdomain/pages(/:postId)': 'pages',
    '/console/:subdomain': 'blogPreview',
    '/console/:subdomain/billing': 'billing',
    '/console/:subdomain/settings(/:type)': 'settings',
    '/console/:subdomain/theme(/:type)': 'theme'

};
const sceneLogic = kea<sceneLogicType>({
    actions: {
        setScene: (scene: string, params: object | null) => ({ scene, params }),
    },
    reducers: {
        scene: [
            null as string | null,
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

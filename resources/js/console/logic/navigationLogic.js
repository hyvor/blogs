import { kea } from "kea";
import api from "../lib/api";


const  navigationLogic = kea({

    key: props => props.subdomain,

    path: key => ['navigation', key],

    actions: {
        getNavigationList: (navigation) => ({navigation}),
        removeFromList: (id) => ({id}),
        addNavigation: (navigation) => ({navigation}),
        updateNavigation: (navigation) => ({navigation}),
    },

    ajax: ({actions, props}) => ({

        load: async ({offset = 0, type}) => {
            const navigation = await api.get(props.subdomain, '/navigation', {
                offset,
                limit: 10,
                type,
            });
            actions.getNavigationList(navigation);
        },

        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/navigation/${id}`);
        },

        create: async ({navigationName, navigationUrl, type}) => {
            const navigation = await api.post(props.subdomain, '/navigation', {
                    navigation_name: navigationName,
                    navigation_url: navigationUrl,
                    type: type 
                })
          
            actions.addNavigation(navigation);
        },

        updateData: async ({userId, navigationName, navigationUrl, type}) => {
            const navigation = await api.put(props.subdomain, `/navigation/${userId}`, {
                    navigation_name: navigationName,
                    navigation_url: navigationUrl,
                    type: type 
                });
            actions.updateNavigation(navigation);
        },
    }),

    reducers: {

        navigation: [[], {
            getNavigationList: (_, {navigation}) => navigation,
            removeFromList: (state, {id}) => state.filter(m => m.id !== id),
            addNavigation: (state, {navigation}) => [navigation, ...state],
            updateNavigation: (state, {navigation}) => [navigation, ...state],
        }]
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default navigationLogic;
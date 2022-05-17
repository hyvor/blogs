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
        updateDestination: (navigation) => ({navigation}),
        updateSource: (navigation) => ({navigation}),
        addNavigationVarian: (navigation) => ({navigation}),
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

        create: async ({name, url, type}) => {
            const navigation = await api.post(props.subdomain, '/navigation', {
                    name: name,
                    url: url,
                    type: type 
                })
          
            actions.addNavigation(navigation);
        },

        createVariant: async ({navigationId, languageId}) => {
            console.log(navigationId, languageId)
            const navigation = await api.post(props.subdomain, '/navigation/variant', {
                id: navigationId,
                languageId: languageId,
            })
            actions.addNavigationVarian(navigation);
        },

        updateData: async ({userId, name, url, type}) => {
            const navigation = await api.put(props.subdomain, `/navigation/${userId}`, {
                    name: name,
                    url: url,
                    type: type 
                });
            actions.updateNavigation(navigation);
        },

        updateItemNumber: async ({NavigationId, destinationId}) => {
            console.log('Navigation' + NavigationId + ' I think its working')
            console.log('Lets see '+ destinationId + ' destination ID')

            const navigation = await api.put(props.subdomain, `/navigation/sort/${NavigationId}`, {
                navigationSort: destinationId,
            });
            actions.updateDestination(navigation);
        },

        updateSourceNav: async ({destinationId, sourceId}) => {
            console.log(destinationId)
            console.log(sourceId + ' source ID')

            const navigation = await api.put(props.subdomain, `/navigation/source/${destinationId}`, {
                    sort: sourceId,
                });
            actions.updateSource(navigation);
        },
    }),

    reducers: {

        navigation: [[], {
            getNavigationList: (_, {navigation}) => navigation,
            removeFromList: (state, {id}) => state.filter(m => m.id !== id),
            addNavigation: (state, {navigation}) => [navigation, ...state],
            updateNavigation: (state, {navigation}) => [navigation, ...state],
            updateDestination: (state, {navigation}) => [navigation, ...state],
            updateSource: (state, {navigation}) => [navigation, ...state],
        }],
        createNewVarian: [[], {
            addNavigationVarian: (state, {navigation}) => [navigation, ...state],
        }],
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default navigationLogic;
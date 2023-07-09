import {actions, events, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";
import {ajax} from "kea-ajax";
import type { routesLogicType } from "./routesLogicType";
import {Route} from "../types";

const routesLogic = kea<routesLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),
    path(key => ['route', key]),

    actions({
        setRoutes: (routes: Route[]) => ({routes}),
        addRoute: (route: Route) => ({route}),
        updateRoute: (route: Route) => ({route}),
        removeFromList: (id: number) => ({id}),
    }),

    ajax(({ actions, props }) => ({

        load: async () => {
            const route = await api.get<Route[]>(props.subdomain, '/routes');
            actions.setRoutes(route);
        },


        create: async ({name, match, template, posts_filter, content_type} : Partial<Route>) => {
            const route = await api.post<Route>(props.subdomain, '/route', {
                name,
                match,
                template,
                posts_filter,
                content_type,
            })
            actions.addRoute(route);
        },

        update: async ({id, match, template, posts_filter, content_type} : Partial<Route>) => {
            const route = await api.patch<Route>(props.subdomain, `/route/${id}`, {
                match,
                template,
                posts_filter,
                content_type,
            });
            actions.updateRoute(route);
        },


        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/route/${id}`);
        },


    })),

    reducers({

        routes: [
            [] as Route[],
            {
                setRoutes: (_, {routes}) => routes,
                removeFromList: (state, {id}) => state.filter(m => m.id !== id),
                addRoute: (state, {route}) => [...state, route],
                updateRoute:(state, {route}) => state.map(
                    stateRoute => stateRoute.id === route.id ? route : stateRoute
                ),
            }
        ],
    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

]);

export default routesLogic;
import {kea, key, path, actions, reducers, props} from 'kea';
import {Tag, User} from "../types";

import type { usersLogicType } from "./usersLogicType";
import {ajax} from "kea-ajax";
import api from "../lib/api";
import {UserRole, UserStatus} from "../enums";
import {diff as getDiff} from "deep-object-diff";

export interface IDKeyedUsers {
    [key: number]: User
}

const usersLogic = kea<usersLogicType<IDKeyedUsers>>([

    props({} as {subdomain: string}),
    key((props) => props.subdomain),
    path((key) => ['users', key]),

    actions({
        addUsers: (users: Array<User>) => ({users}),
        setUsersList: (usersList: number[]) => ({usersList}),
        setUsersListHasMore: (hasMore: boolean) => ({hasMore}),
        removeUser: (id: number) => ({id}),
    }),

    ajax(({props, values, actions}) => ({

        load: async () => {
            const users =  await api.get<User[]>(props.subdomain, '/users');
            actions.addUsers(users)
            actions.setUsersList(users.map(user => user.id));
            actions.setUsersListHasMore(users.length === 50);
        },

        loadMore: async ({offset}) => {
            const users =  await api.get<User[]>(props.subdomain, '/users', {offset});
            actions.addUsers(users)
            actions.setUsersList([...values.usersList, ...users.map(user => user.id)]);
            actions.setUsersListHasMore(users.length === 50);
        },

        create: async (
            {usernameOrEmail, role, onCreate} :
            {usernameOrEmail: string, role: UserRole, onCreate: Function}
        ) => {
            const user = await api.post<User>(props.subdomain, '/user', {
                username_or_email: usernameOrEmail,
                role
            })
            actions.addUsers([user]);
            actions.setUsersList([...values.usersList, user.id]);
            onCreate()
        },

        createGuest: async ({name, onCreate} : {name: string, onCreate: Function}) => {
            const user = await api.post<User>(props.subdomain, '/user/guest', {
                name
            })
            actions.addUsers([user]);
            actions.setUsersList([...values.usersList, user.id]);
            onCreate();
        },

        update: async ({user, onUpdate} : {user: User, onUpdate: Function}) => {
            const userOriginal = values.users[user.id]

            const diff = getDiff(userOriginal, user) as Partial<Tag>

            if (diff.variants) {

                for (const variant of user.variants) {

                    const variantOriginal = userOriginal.variants.find(t => t.language_id === variant.language_id)

                    if (!variantOriginal)
                        continue;

                    const variantDiff = getDiff(variantOriginal, variant)

                    await api.patch(props.subdomain, `/user/${user.id}/variant`, {
                        ...variantDiff,
                        language_id: variant.language_id
                    })

                }

                delete diff.variants;

            }

            const newUser = await api.patch<User>(props.subdomain, `/user/${user.id}`, diff)

            actions.addUsers([newUser])

            onUpdate();
        },

        resendInvite: async ({id} : {id: number}) => {
            await api.post(props.subdomain, `/user/${id}/resend-invite`);
        },

        remove: async ({id} : {id: number}) => {
            actions.removeUser(id);
            await api.delete(props.subdomain, `/user/${id}`);
        }

    })),

    reducers({

        users: [
            {} as IDKeyedUsers,
            {
                addUsers: (state, { users } : { users: Array<User> }) => {
                    const usersKeyed : IDKeyedUsers = {};
                    for (let user of users) {
                        usersKeyed[user.id] = user;
                    }

                    return {...state, ...usersKeyed}
                }
            }
        ],

        usersList: [
            [] as number[],
            {
                setUsersList: (_, {usersList}) => usersList,
                removeUser: (state, {id}) => state.filter(stateId => stateId !== id)
            }
        ],

        usersListHasMore: [
            false,
            {
                setUsersListHasMore: (_, {hasMore}) => hasMore
            }
        ]

    })
])

export default usersLogic
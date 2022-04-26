
/**
 * A better version of [kea-loaders](https://github.com/keajs/kea-loaders) plugin
 * 
 * Instead of boolean loading status, this plugin uses loading|success|error status
 * Saves the error message so you can show it to the user
 * Supports loadMore
 */

/**
 * Docs
 * 
 * loadersWithHasMore: {
 *      posts: {
 *           getPosts: () => {}
 *      },
 *  }
 *
 * This code generates these:
 * 
 * Actions
 *  - getPostsLoad
 *  - getPostsLoadSuccess
 *  - getPostsLoadError
 *  - getPostsLoadMore
 *  - getPostsLoadMoreSuccess
 *  - getPostsLoadMoreError
 *  - getPostsSetHasMore
 * 
 * Reducers
 *  - posts
 *  - postsLoadStatus = loading|success|error
 *  - postsLoadError
 *  - postsLoadMoreStatus = loading|success|error
 *  - postsLoadMoreError
 *  - postsHasMore = false
 */

export const loadersWithHasMorePlugin = (options) => {

    return {
        name: 'loadersWithHasMore',

        buildSteps: {
            loadersWithHasMore(logic, input) {
                if (!input.loadersWithHasMore)
                    return;

                // run the loaders function with the already created logic as an input,
                // so it can do ({ actions, ... }) => ({ ... })
                const loaders = typeof input.loadersWithHasMore === 'function' ? input.loadersWithHasMore(logic) : input.loaders

                for (const [reducerKey, actionsObject] of Object.entries(loaders)) {
                    let defaultValue = logic.defaults[reducerKey] || null

                    if (Array.isArray(actionsObject)) {
                        if (typeof defaultValue === 'undefined') {
                            defaultValue = actionsObject[0]
                        }
                        actionsObject = actionsObject[1] || {}
                    }



                    logic.extend({
                        actions: () => {
                            const newActions = {}

                            for (const [actionKey] of Object.entries(actionsObject)) {
                                newActions[actionKey + "Load"] = (params) => params
                                newActions[actionKey + "LoadSuccess"] = (value) => ({ value })
                                newActions[actionKey + "LoadError"] = (error) => ({ error })
                                newActions[actionKey + "LoadMore"] = (params) => params
                                newActions[actionKey + "LoadMoreSuccess"] = (value) => ({value})
                                newActions[actionKey + "LoadMoreError"] = (error) => ({error})

                                newActions[actionKey + "SetHasMore"] = (hasMore) => ({hasMore})
                            }

                            return newActions
                        },

                        reducers: ({actions}) => {
                            const reducerObject = {};
                            const reducerLoadStatusObject = {};
                            const reducerLoadErrorObject = {};

                            const reducerLoadMoreStatusObject = {};
                            const reducerLoadMoreErrorObject = {};

                            const reducerHasMoreObject = {};

                            for (const [actionKey] of Object.entries(actionsObject)) {
                                reducerObject[actionKey + "LoadSuccess"] = (_, {value}) => value
                                reducerObject[actionKey + "LoadMoreSuccess"] = (state, {value}) => [...state, ...value]

                                // load
                                reducerLoadStatusObject[actionKey + "Load"] = () => "loading"
                                reducerLoadStatusObject[actionKey + "LoadSuccess"] = () => "success"
                                reducerLoadStatusObject[actionKey + "LoadError"] = () => "error"

                                reducerLoadErrorObject[actionKey + "LoadError"] = (_, {error}) => error

                                // load more
                                reducerLoadMoreStatusObject[actionKey + "LoadMore"] = () => "loading"
                                reducerLoadMoreStatusObject[actionKey + "LoadMoreSuccess"] = () => "success"
                                reducerLoadMoreStatusObject[actionKey + "LoadMoreError"] = () => "error"

                                reducerLoadMoreErrorObject[actionKey + "LoadMoreError"] = (_, {error}) => error;

                                // has more
                                reducerHasMoreObject[actionKey + "SetHasMore"] = (_, {hasMore}) => hasMore;
                            }

                            const newReducers = {};

                            newReducers[reducerKey] = [defaultValue, reducerObject]
                            newReducers[reducerKey + "LoadStatus"] = [null, reducerLoadStatusObject]
                            newReducers[reducerKey + "LoadError"] = [null, reducerLoadErrorObject]
                            newReducers[reducerKey + "LoadMoreStatus"] = [null, reducerLoadMoreStatusObject]
                            newReducers[reducerKey + "LoadMoreError"] = [null, reducerLoadMoreErrorObject]

                            newReducers[reducerKey + "HasMore"] = [false, reducerHasMoreObject]

                            return newReducers
                            
                        },

                        listeners: ({actions}) => {
                            const newListeners = {};

                            for (const [actionKey, listener] of Object.entries(actionsObject)) {
                                function callFunc(addPart) {
                                    return (payload = {}, breakpoint, action) => {
                                        try {
                                            const response = listener(payload, breakpoint, action);
                                            if (response && response.then && typeof response.then === 'function') {
                                                return response
                                                    .then(asyncResponse => actions[actionKey + addPart + "Success"](asyncResponse))
                                                    .catch(error => actions[actionKey + addPart + "Error"](error.message))
                                            } else {
                                                actions[actionKey + addPart + "Success"](response)
                                            }
                                        } catch (error) {
                                            actions[actionKey + addPart + "Error"](error.message)
                                        }
                                    }
                                }
                                newListeners[actionKey + 'Load'] = callFunc('Load')
                                newListeners[actionKey + 'LoadMore'] = callFunc('LoadMore');
                            }
                            return newListeners
                        }

                    })

                }

            }
        },

        buildOrder: {
            loaders: { after: 'defaults' },
        },
    }


}
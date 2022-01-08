
/**
 * A simpler version of [kea-loaders](https://github.com/keajs/kea-loaders) plugin
 * 
 * This plugin only saves loading state of the HTTP call and error if any.
 * Actual data has to be managed seperately.
 * This library has nothing to do with what you are doing with the HTTP repsonse (Do anything!)
 * 
 * - Instead of boolean loading status, this plugin uses loading|success|error status
 * - Saves the error message so you can show it to the user
 */

/**
 * Docs
 * 
 * ajax: {
 *     createPost: async () => {
 *          // do anything
 *     }
 * }
 *
 * 
 * Creates a reducer
 *  createPost: {
 *      status: (string) loading|success|error
 *      error: null|string
 *  }
 *  
 * Creates these actions
 * createPost
 * createPostAjaxStart
 * createPostAjaxSuccess
 * createPostAjaxError
 * 
 * How to call
 * 
 * createPost({}) // data is always an object
 */

 export const ajaxPlugin = (options) => {

    return {
        name: 'ajax',

        buildSteps: {
            ajax(logic, input) {
                if (!input.ajax)
                    return;

                // run the loaders function with the already created logic as an input,
                // so it can do ({ actions, ... }) => ({ ... })
                const ajax = typeof input.ajax === 'function' ? input.ajax(logic) : input.ajax

                for (const [key, handler] of Object.entries(ajax)) {
                    
                    logic.extend({

                        actions: () => {
                            const newActions = {};
                            newActions[key] = (params) => params || {};
                            newActions[key + "AjaxStart"] = false;
                            newActions[key + "AjaxSuccess"] = false;
                            newActions[key + "AjaxError"] = (error) => ({error});

                            return newActions;
                        },

                        reducers: () => {
                            const newReducers =  {};

                            newReducers[key] = [{
                                status: 'loading',
                                error: null
                            }, {
                                [key + "AjaxStart"]: () => ({
                                    status: "loading",
                                    error: null
                                }),
                                [key + "AjaxSuccess"]: () => ({
                                    status: "success",
                                    error: null
                                }),
                                [key + "AjaxError"]: (_, {error}) => ({
                                    status: "error",
                                    error
                                })
                            }]

                            return newReducers;
                            
                        },

                        listeners: ({actions}) => {
                            const newListeners = {};

                            newListeners[key] = (payload = {}, breakpoint, action) => {
                                actions[key + "AjaxStart"]();
                                try {
                                    const response = handler(payload, breakpoint, action);
                                    if (response && response.then && typeof response.then === "function") {
                                        return response
                                            .then(() => actions[key + "AjaxSuccess"]())
                                            .catch(error => actions[key + "AjaxError"](error.message))
                                    } else {
                                        actions[key + "AjaxSuccess"]();
                                    }
                                } catch (error) {
                                    actions[key + "AjaxError"](error.message)
                                }
                            }

                            return newListeners
                        }
                    });

                }

            }
        },

        buildOrder: {
            ajax: { after: 'defaults' },
        },
    }


}
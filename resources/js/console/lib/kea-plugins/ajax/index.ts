
/**
 * A simpler version of [kea-loaders](https://github.com/keajs/kea-loaders) plugin
 * 
 * This plugin only saves loading state of the HTTP call and error if any.
 * Actual data has to be managed separately.
 * This library has nothing to do with what you are doing with the HTTP response (Do anything!)
 * 
 * - Instead of boolean loading status, this plugin uses loading|success|error status
 * - Saves the error message so you can show it to the user
 */

import {KeaPlugin, ListenerFunction} from "kea";

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
 *  createPostAjax: {
 *      status: (string) loading|success|error
 *      error: null|string
 *  }
 *  
 * Creates these actions
 * createPost
 * createPostStart
 * createPostSuccess
 * createPostError
 * 
 * How to call
 * 
 * createPost({}) // data is always an object
 */

type AjaxReducerType = {
    status: 'loading' | 'success' | 'error' | null,
    error: string | null
};

 export const ajaxPlugin = () : KeaPlugin => {

    return {
        name: 'ajax',

        buildSteps: {
            ajax(logic, input) {
                if (!input.ajax)
                    return;

                // run the loaders function with the already created logic as an input,
                // so it can do ({ actions, ... }) => ({ ... })
                const ajax: Record<string, Function> = typeof input.ajax === 'function' ? input.ajax(logic) : input.ajax

                for (const [key, handler] of Object.entries(ajax)) {
                    
                    logic.extend({

                        actions: () => {
                            const newActions: Record<string, (...args: any[]) => any> = {};
                            newActions[key] = (params) => params || {};
                            newActions[key + "Start"] = () => false;
                            newActions[key + "Success"] = () => false;
                            newActions[key + "Error"] = (error) => ({error});

                            return newActions;
                        },

                        reducers: () => {
                            const newReducers: Record<string, [
                                AjaxReducerType,
                                { [x: string]: (state: any, payload: any) => AjaxReducerType }
                            ]> =  {};

                            newReducers[key + "Ajax"] = [{
                                status: null,
                                error: null
                            }, {
                                [key + "Start"]: () => ({
                                    status: 'loading',
                                    error: null
                                }),
                                [key + "Success"]: () => ({
                                    status: 'success',
                                    error: null
                                }),
                                [key + "Error"]: (_, {error}) => ({
                                    status: 'error',
                                    error
                                })
                            }]

                            return newReducers;
                            
                        },

                        listeners: ({actions}) => {
                            const newListeners: Record<string, ListenerFunction> = {};

                            newListeners[key] = (payload = {}, breakpoint, action) => {
                                actions[key + "Start"]();
                                try {
                                    const response = handler(payload, breakpoint, action);
                                    if (response && response.then && typeof response.then === "function") {
                                        return response
                                            .then(() => actions[key + "Success"]())
                                            .catch((error: Error) => {
                                                actions[key + "Error"](error.message)
                                                throw error;
                                            })
                                    } else {
                                        actions[key + "Success"]();
                                    }
                                } catch (error) {
                                    actions[key + "Error"](error.message)
                                    throw error;
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
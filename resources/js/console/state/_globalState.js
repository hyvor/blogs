import { useGlobalState } from "state-pool";

/**
 * This function kind of extends useGlobalState
 * to have get(), set(), update(), and other user-defined functions
 */
export default function _globalState(key, def = null, functions = {}) {

    const [state, setState, updateState] = useGlobalState(key, {
        default: def
    });

    var defFuncs = {
        get: () => state,
        set: setState,
        update: updateState,
    };

    // merge with functions
    for (var i in functions) {
        if (!defFuncs[i]) {
            // sends the params inside to custom function
            defFuncs[i] = (...params) => {
                return functions[i](...params, state, setState, updateState);
            }
        }
    }

    return defFuncs

}
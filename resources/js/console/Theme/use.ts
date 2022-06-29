import themeLogic from "../logic/themeLogic";
import getSubdomain from "../logic-helpers/subdomain";
import {useActions, useValues} from "kea";


export function buildThemeLogic() {
    return themeLogic({subdomain: getSubdomain()})
}
export function useThemeValues() {
    return useValues(buildThemeLogic())
}
export function useThemeActions() {
    return useActions(buildThemeLogic())
}
import {useActions, useValues} from "kea";
import navigationLogic from "../../logic/navigationLogic";
import getSubdomain from "../../logic-helpers/subdomain";

export function useNavigationActions() {
    return useActions(navigationLogic({subdomain: getSubdomain()}))
}

export function useNavigationValues() {
    return useValues(navigationLogic({subdomain: getSubdomain()}))
}
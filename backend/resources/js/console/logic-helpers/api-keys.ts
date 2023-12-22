

import {useActions, useValues} from "kea";
import apiKeysLogic from "../logic/apiKeysLogic";
import getSubdomain from "./subdomain";

export function getApiKeysLogic() {
    return apiKeysLogic({subdomain: getSubdomain()})
}

export function useApiKeysValues() {
    return useValues(getApiKeysLogic())
}
export function useApiKeysActions() {
    return useActions(getApiKeysLogic());
}
import tagsLogic from "../../logic/tagsLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {useActions, useValues} from "kea";

export function getTagsLogic() {
    return tagsLogic({subdomain: getSubdomain()})
}

export function useTagsValues() {
    return useValues(getTagsLogic())
}

export function useTagsActions() {
    return useActions(getTagsLogic());
}
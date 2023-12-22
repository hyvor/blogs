import languagesLogic from "../../logic/languagesLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {useValues} from "kea";


export function useLanguagesValues() {
    return useValues(languagesLogic({subdomain: getSubdomain()}))
}
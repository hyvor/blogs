import {useActions, useValues} from "kea";
import usersLogic from "../logic/usersLogic";
import getSubdomain from "./subdomain";

export function getUsersLogic() {
    return usersLogic({subdomain: getSubdomain()})
}

export function useUsersValues() {
    return useValues(getUsersLogic())
}
export function useUsersActions() {
    return useActions(getUsersLogic());
}
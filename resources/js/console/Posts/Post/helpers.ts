import { useActions, useValues } from "kea";
import postLogic from "../../logic/postLogic";

export function usePostValues(id: number) {
    return useValues(postLogic({id}));
}

export function usePostActions(id: number) {
    return useActions(postLogic({id}))
}
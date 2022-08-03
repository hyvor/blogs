import {useEffect} from "react";
import onOutsideClick from "../../../helpers/onOutsideClick";
import { useActions, useValues } from "kea";
import postLogic from "../../logic/postLogic";

export function usePostValues(id: number) {
    return useValues(postLogic({id}));
}

export function usePostActions(id: number) {
    return useActions(postLogic({id}))
}


export function useAutoSave(id: number) {

    const { currentVariant, diff, editorState } = usePostValues(id);
    const { savePost } = usePostActions(id)

    function handleAutoSave() {
        if (!editorState.isUnpublishing && !editorState.isPublishing && !editorState.isNonDraftUpdating) {
            savePost();
        }
    }

    useEffect(() => {

        // auto save
        const autoSaveInterval = setInterval(handleAutoSave, 10000);

        function checkSave(e: KeyboardEvent) {
            if (e.key === 's' && (e.ctrlKey || e.metaKey)) { // ctrl + s
                handleAutoSave();
                e.preventDefault();
            }
        }

        function checkSaveUnload(event: BeforeUnloadEvent) {
            if (
                (currentVariant.status === 'published' || currentVariant.status === 'scheduled') &&
                Object.keys(diff).length > 0
            ) {
                event.returnValue = 'Are you sure to close this tab?';
            } else {
                handleAutoSave();
            }
        }

        function checkSavePopstate(event: PopStateEvent) {
            console.log(event)
            event.preventDefault()
        }

        // save on CTRL + S
        window.addEventListener('keydown', checkSave);
        // save on unload
        window.addEventListener('beforeunload', checkSaveUnload);
        // save on popstate change (internal navigation)
        window.addEventListener('popstate', checkSavePopstate);

        // save on outsideClick
        // const removeOutsideEvent = onOutsideClick(viewRef.current, handleAutoSave, false, false, false);

        return () => {
            clearInterval(autoSaveInterval)
            window.removeEventListener('keydown', checkSave);
            window.removeEventListener('beforeunload', checkSaveUnload);
            window.removeEventListener('popstate', checkSavePopstate);
            // removeOutsideEvent(false);
        }

    }, [id, diff])

}
import {usePostActions, usePostValues} from "./helpers";
import {useEffect} from "react";

export default function useSave(id: number) {

    const { currentVariant, currentVariantDiff } = usePostValues(id);
    const { saveCurrentVariantDiff, changeEditorState } = usePostActions(id)

    function handleAutoSave(onSave?: Function) {

        const diff = {} as {
            content?: string,
            content_unsaved?: string,
            title?: string,
        }

        if (currentVariantDiff.title) {
            diff['title'] = currentVariantDiff.title;
        }
        if (currentVariantDiff.content) {
            diff['content'] = currentVariantDiff.content;
        }
        if (currentVariantDiff.content_unsaved) {
            diff['content_unsaved'] = currentVariantDiff.content_unsaved;
        }

        if (Object.keys(diff).length > 0) {
            
            changeEditorState('isSaving', true);

            saveCurrentVariantDiff({
                diff,
                onSave: () => {
                    changeEditorState('isSaving', false);
                    onSave?.();
                }
            })

        } else {
            onSave?.();
        }

    }

    function hasPublishedChanged() {
        return (currentVariant.status === 'published' || currentVariant.status === 'scheduled') &&
            (
                currentVariantDiff.title ||
                currentVariantDiff.content_unsaved
            );  
    }

    async function handleBack() {
        await new Promise(resolve => handleAutoSave(() => {
            resolve(null);
        }));
    }

    useEffect(() => {

        // auto save
        const autoSaveInterval = setInterval(handleAutoSave, 15000);

        function checkSave(e: KeyboardEvent) {
            if (e.key === 's' && (e.ctrlKey || e.metaKey)) { // ctrl + s
                handleAutoSave();
                e.preventDefault();
            }
        }

        function checkSaveUnload(event: BeforeUnloadEvent) {
            if (hasPublishedChanged()) {
                event.returnValue = 'Are you sure to close this tab? You have unsaved changes.';
            } else {
                handleAutoSave();
            }
        }

        // save on CTRL + S
        window.addEventListener('keydown', checkSave);
        // save on unload
        window.addEventListener('beforeunload', checkSaveUnload);
    
        return () => {
            clearInterval(autoSaveInterval)
            window.removeEventListener('keydown', checkSave);
            window.removeEventListener('beforeunload', checkSaveUnload);
            //window.removeEventListener('popstate', checkSavePopstate);
        }

    }, [id, currentVariantDiff, currentVariant])

    return {
        onBack: handleBack
    }

}
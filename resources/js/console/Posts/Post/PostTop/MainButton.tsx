import {CaretDownFill, PencilFill} from "react-bootstrap-icons";
import ActionButton from "../../../ReusableComponents/ActionButton";
import React, {useEffect, useState} from "react";
import {usePostActions, usePostValues} from "../helpers";
import {toast} from "react-toastify";
import {Post, PostVariant} from "../../../types";

export default  function MainButton({id} : {id: number}) {
    let name, onClick: any, icon;

    const { currentVariant, editorState, forceSavePostAjax, diff } = usePostValues(id)
    const { changeEditorState, forceSavePost, savePost } = usePostActions(id)
    const hasChanged = currentVariant.content_unsaved !== currentVariant.content;


    useEffect(() => {
        // Make the editor editable when the post is loaded
        changeEditorState('isNonDraftEditing', true);
    }, []);

    function handleUpdateNonDraft() {

        if (!currentVariant.content_unsaved) {
            changeEditorState('isNonDraftEditing', false);
            savePost();
            return;
        }

        changeEditorState('isNonDraftUpdating', true);

        const update = {} as Partial<Post>
        const variant = {
            language_id: editorState.languageId as number,
            content: currentVariant.content_unsaved,
            content_unsaved: null
        } as Partial<PostVariant>;
        update.variants = [variant as PostVariant];

        forceSavePost({
            update,
            onSave: (p: Post) => {
                changeEditorState('isNonDraftUpdating', false)
                toast.success(
                    <div>Post Updated. <a
                        className="link"
                        href={p.variants.find(v => v.language_id === editorState.languageId)?.url}
                        target="_blank"
                    >View</a></div>,
                    {
                        autoClose: 5000
                    }
                )
            }
        });
    }

    if ((currentVariant.status === 'published' || currentVariant.status === 'scheduled')) {
        return hasChanged ? <ActionButton
            className="small main-button"
            status={!editorState.isNonDraftUpdating ? "stale" : (forceSavePostAjax.status || 'stale')}
            staleName="Save changes"
            loadingName="Saving..."
            successName="Saved"
            errorName="Try again"
            staleOnClick={handleUpdateNonDraft}
            errorOnClick={handleUpdateNonDraft}
        /> : null;
    } else {
        name = "Publish";
        onClick = () => changeEditorState('isPublishing', true);
        icon = <CaretDownFill />;
    }

    return <button className="button small main-button" onClick={() => onClick()}>
        <span>{name}</span>{icon}
    </button>
}
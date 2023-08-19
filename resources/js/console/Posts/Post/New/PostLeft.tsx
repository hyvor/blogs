import React from "react";
import Editor from "../ProseMirror/Editor";
import { usePostActions, usePostValues } from "../helpers";
import PostLanguageSelector from "../PostLanguageSelector";
import TitleRow from "../PostTop/TitleRow";
import {useLanguagesValues} from "../../../Settings/Languages/helpers";
import { BoxArrowUpRight, InfoCircle } from "react-bootstrap-icons";
import { getBlogUrl } from "../../../lib/blog-helpers";
import getSubdomain from "../../../logic-helpers/subdomain";
import Loader from "../../../ReusableComponents/Loader";
import PublishButton from "./Publish/PublishButton";
import UnpublishButton from "./Publish/UnpublishButton";

export default function PostLeft({id, postViewRef} : {id: number, postViewRef: React.RefObject<HTMLDivElement>}) {

    const { 
        post, 
        currentLanguage, 
        currentVariant,
        currentVariantOriginal, 
        editorState 
    } = usePostValues(id);

    const hasTitleOrContentChanged = 
        currentVariantOriginal.title !== currentVariant.title ||
        currentVariantOriginal.content !== currentVariant.content ||
        currentVariantOriginal.content_unsaved !== currentVariant.content_unsaved;

    return <div className="post-left">

        <div className="post-left-header">

            <div className="left-header-row">

                <div className="left-header-left">
                    <PostLanguageSelector id={id} />
                </div>

                <div className="left-header-right">

                    <span 
                        className={`global-post-status ${currentVariant.status} large`}
                    >{currentVariant.status}</span>

                    <a
                        href={getBlogUrl(getSubdomain(), '/p/' + post.preview_id + "/" + currentLanguage.code)}
                        target="_blank"
                        data-testid="preview-button"
                    >
                        <button className="button medium light view" style={{marginRight: 8}}>
                            <span>Preview</span>&nbsp;<BoxArrowUpRight />
                        </button>
                    </a>
                    
                    <UnpublishButton id={id} />
                    <PublishButton id={id} />

                </div>

            </div>

            <div className="left-header-row additional-data">

                <div className="left-header-left">
                    <span className="saver">

                        {
                            editorState.isSaving ?
                                <span>
                                    <Loader size="extra-mini" inline={true} />
                                    <span className="saving-name">Saving</span>
                                </span>
                                :

                                (
                                    hasTitleOrContentChanged ?
                                        <span className="not-saved">Unsaved changes *</span> :
                                        <span className="saved">Saved</span>
                                )

                        }

                    </span>
                </div>

                <div className="left-header-right">
                    
                    <span className="words-count" id="pm-word-count"/>

                    <a target="_blank" href="/docs/writing" className="help">
                        <InfoCircle />
                    </a>

                </div>

            </div>

        </div>

        <div className="post-left-body">
            <div className="post-left-title">
                <TitleRow id={id} />
            </div>

            <div className="post-left-editor">
                <PostEditor id={id} />
            </div>
        </div>


    </div>

}


function PostEditor({id} : {id: number}) {

    const { currentVariant, editorState } = usePostValues(id)
    const { updateCurrentPostVariantValue } = usePostActions(id)

    const { getLanguageById } = useLanguagesValues();
    const language = getLanguageById(editorState.languageId);
    const isRtl = language ? language.direction === 'rtl' : false;

    const isNonDraft = currentVariant.status !== 'draft';

    const content = isNonDraft ? (currentVariant.content_unsaved || currentVariant.content) : currentVariant.content;

    function handleContentUpdate(value: string) {
        const key = isNonDraft ? 'content_unsaved' : 'content'
        updateCurrentPostVariantValue(key, value)
    }

    return <Editor
        id={id}
        value={content || ''}
        currentLanguageId={editorState.languageId}
        status={currentVariant.status}
        version={editorState.version}
        onChange={(v: string) => handleContentUpdate(v)}
    />

}
import React, { useEffect, useState } from "react";
import Editor from "../ProseMirror/Editor";
import { usePostActions, usePostValues } from "../helpers";
import PostLanguageSelector from "../PostLanguageSelector";
import TitleRow from "./TitleRow";
import {useLanguagesValues} from "../../../Settings/Languages/helpers";
import { BoxArrowUpRight, InfoCircle } from "react-bootstrap-icons";
import { getBlogUrl } from "../../../lib/blog-helpers";
import getSubdomain from "../../../logic-helpers/subdomain";
import Loader from "../../../ReusableComponents/Loader";
import PublishButton from "./Publish/PublishButton";
import UnpublishButton from "./Publish/UnpublishButton";
import { OutsideClick } from "../../../ReusableComponents/OutsideClick";
import { AutoTranslateWrap } from "../AutoTranslate";

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

    const hasPublishedContentChanged = currentVariant.content_unsaved !== currentVariant.content && 
        currentVariant.content_unsaved !== null;
    
    const { changeEditorState } = usePostActions(id);


    const { getLanguageById } = useLanguagesValues();
    const language = getLanguageById(editorState.languageId);
    const isRtl = language ? language.direction === 'rtl' : false;

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

                    
                    <PreviewButton id={id} />
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
                                    <div className="saver-information">
                                        {
                                                 
                                                 hasTitleOrContentChanged ?
                                                 <span className="not-saved">Unsaved changes *</span> :
                                                 <span className="saved">Saved</span>
                                        }
                                        {
                                                           

                                            currentVariant.status === 'published' && hasPublishedContentChanged ?
                                                <div className="published-edit text-edit">
                                                    <span>Unpublished changes.</span>
                                                    <div 
                                                        className="discard-changes" 
                                                        onClick={() => changeEditorState('isDiscarding', true)}
                                                    >
                                                        Discard
                                                    </div>
                                                </div> 
                                            : <div></div>
                                        }
                                    </div>
                                    

                                        
                                )    

                        }

                    {
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

        <div 
            className="post-left-body"
        >
            <div
                className="post-editor-wrap"
                spellCheck={false}
                dir={isRtl ? 'rtl' : 'ltr'}
                style={isRtl ? {
                    direction: 'rtl',
                    textAlign: 'right'
                } : undefined}
            >
                <div className="post-left-title">
                    <TitleRow id={id} />
                </div>

                <div className="post-left-editor">
                    <PostEditor id={id} />

                    {
                        !currentLanguage.is_primary && <AutoTranslateWrap id={id} />
                    }
                </div>
            </div>
        </div>


    </div>

}

function PreviewButton({id} : {id: number}) {

    const { post, currentLanguage, currentVariant } = usePostValues(id);

    const [isPopupOpen, setPopupOpen] = useState(false);

    const previewUrl = getBlogUrl(getSubdomain(), '/p/' + post.preview_id + "/" + currentLanguage.code)

    function handleNewTabOpen(url: string) {
        window.open(url, '_blank')
    }

    return <a
        href={
            currentVariant.status === 'published' ?
                undefined :
                previewUrl
            }
        onClick={(e) => {
            e.stopPropagation();
            if (currentVariant.status === 'published') {
                setPopupOpen(!isPopupOpen)
            }
        }}
        target="_blank"
        data-testid="preview-button-link"
        className="preview-button-link"
    >
        <button className="button medium light view" style={{marginRight: 8}}>
            <span>
                {
                    currentVariant.status === 'published' ?
                        'View' :
                        'Preview'
                }    
            </span>&nbsp;<BoxArrowUpRight />
        </button>

        {
            isPopupOpen &&
            <OutsideClick onClick={() => setPopupOpen(false)}>
                <div 
                    className="preview-type-popup"
                    onClick={(e) => e.stopPropagation()}
                >
                    <button
                        className="button medium light"
                        onClick={() => handleNewTabOpen(previewUrl)}
                    >
                        <span className="name">Preview</span>
                        <BoxArrowUpRight />
                    </button>
                    <button
                        className="button medium light published"
                        onClick={() => handleNewTabOpen(currentVariant.url)}
                    >
                        <span className="name">Published Post</span>
                        <BoxArrowUpRight />
                    </button>
                </div>
            </OutsideClick>
        }
    </a>

}


function PostEditor({id} : {id: number}) {

    const { currentVariant, editorState } = usePostValues(id)
    const { updateCurrentPostVariantValue } = usePostActions(id)

    const isNonDraft = currentVariant.status !== 'draft';

    const content = isNonDraft ? (currentVariant.content_unsaved || currentVariant.content) : currentVariant.content;

    function handleContentUpdate(value: string) {
        const key = isNonDraft ? 'content_unsaved' : 'content'
        updateCurrentPostVariantValue(key, value)
    }

    useEffect(() => {

        if (
            currentVariant.title &&
            editorState.editorView &&
            editorState.editorView.state.doc.textContent === ''
        ) {
            editorState.editorView.focus();
        }

    }, [editorState.editorView]);

    return <Editor
        id={id}
        value={content || ''}
        currentLanguageId={editorState.languageId}
        status={currentVariant.status}
        version={editorState.version}
        onChange={(v: string) => handleContentUpdate(v)}
    />

}
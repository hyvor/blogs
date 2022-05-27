import React, { useEffect, useRef, useState } from 'react';
import onOutsideClick from '../../../helpers/onOutsideClick';
import Loader from '../../ReusableComponents/Loader';
import { toast } from 'react-toastify';
import Tooltip from '../../ReusableComponents/Tooltip';
import {Post, PostVariant} from "../../types";
import {usePostValues, useAutoSave} from "./helpers";
import PostTop from "./PostTop/PostTop";
import PostBottom from "./PostBottom";
import PostMiddle from "./PostMiddle";

let publisherOutsideCleaner: any;

export default function Post( { id }: { id: number }) {

    const { loadPostAjax, editorState } = usePostValues(id)

    useAutoSave(id);

    if (loadPostAjax.status === 'loading') {
        return <div className="post-loading">
            <Loader />
        </div>;
    }

    return <div className={"post-editor" + (editorState.isFullscreen ? " fullscreen" : "") }>

        <div className="pos-rel"> {/* this element is required to make the tooltip work correctly */}

            <PostTop id={id} />
            <PostMiddle id={id} />
            <PostBottom id={id} />

            <Tooltip place="bottom" />

            {/*<Unpublisher />*/}

        </div>

    </div>

}

export function Postold( {subdomain, id} : {subdomain: string, id: number} ) {

    /*const postLogicInst = postLogic({id});
    const { post, loadPostAjax, savePostAjax, forceSavePostAjax, diff } = useValues(postLogicInst)
    const { updatePostValue, updatePostVariantValue, savePost, createVariant, forceSavePost } = useActions(postLogicInst)

    const { findBlogBySubdomain } = useValues(blogsLogic)

    const { languages, getLanguageById } = useValues(languagesLogic({subdomain}))
 
    const [isFullScreen, setIsFullScreen] = useState(false);

    const [currentLanguageId, setCurrentLanguageId] = useState( findBlogBySubdomain(subdomain).blog.default_language.id );
    const currentLanguage = getLanguageById(currentLanguageId);*/

    /*if (!currentLanguage) {
        return null;
    }*/

    /*if (loadPostAjax.status === 'loading') {
        return <div className="post-loading">
            <Loader />
        </div>;
    }*/

    /**
     * Disallow outside clicking when the content has changed
     */
    const viewRef = useRef(null);

    const variants = post ? (post.variants || []) : {};
    const variant = variants[currentLanguageId] || {} as PostVariant;

    function handleAutoSave() {
        if (!holdAutoSavingRef.current) {
            savePost();
        }
    }

    // saving
    /*
    useEffect(() => {

        // auto save
        const autoSaveInterval = setInterval(handleAutoSave, 10000);

        function checkSave(e: KeyboardEvent) {
            if (e.key === 's' && (e.ctrlKey || e.metaKey)) { // ctrl + s
                handleAutoSave();
                e.preventDefault();
            }
        } 

        function checkSaveUnload() {
            if (
                (variant.status === 'published' || variant.status === 'scheduled') && 
                Object.keys(diff).length > 0
            ) {
                return true;
            } else {
                handleAutoSave();
            }
        }

        // save on CTRL + S
        window.addEventListener('keydown', checkSave);
        // save on unload
        window.addEventListener('beforeunload', checkSaveUnload);

        // save on outsideClick
        const removeOutsideEvent = onOutsideClick(viewRef.current, handleAutoSave, false, false, false);

        return () => {
            clearInterval(autoSaveInterval)
            window.removeEventListener('keydown', checkSave);
            window.removeEventListener('beforeunload', checkSaveUnload);
            removeOutsideEvent(false);
        }
    }, [id]) */

    /**
     * Settings view
     * Close on outside click
     */
    const [isSettingsOpen, setIsSettingsOpen] = useState(false);
    const settingsViewRef = useRef(null);

    /**
     * Publishing view
     */
    const [isPublisherOpen, setIsPublisherOpen] = useState(false);
    const publisherViewRef = useRef(null);

    /*function openSettingsView() {
        setIsSettingsOpen(true);
        onOutsideClick(settingsViewRef.current, closeSettingsView);
    }
    function closeSettingsView() {
        setIsSettingsOpen(false);
    }*/

    function openPublisher() {
        setIsPublisherOpen(true)
        publisherOutsideCleaner = onOutsideClick(publisherViewRef.current, closePublisher);
    }
    function closePublisher() {
        setIsPublisherOpen(false)
        publisherOutsideCleaner && publisherOutsideCleaner()
    }

    /*function UnPublishButton() {
        let name;
        if (variant.status === 'published') {
            name = "Unpublish";
        } else if (variant.status === 'scheduled') {
            name = "Unschedule";
        }

        return name ? <button 
            className="button small secondary unpublish-button"
            onClick={() => setIsUnPublishing(true)}
        >
            {name}
        </button> : null;
    }*/
    /*function MainButton() {
        let name, onClick: any, icon;

        if (variant.status === 'published' || variant.status === 'scheduled') {
            if (!nonDraftPostEditing) {
                name = "Edit";
                icon = <PencilFill />
                onClick = () => setNonDraftPostEditing(true);
            } else {
                return <ActionButton 
                    className="small main-button"
                    status={!isNonDraftUpdating ? "stale" : forceSavePostAjax.status!}
                    staleName="Update"
                    loadingName="Updating" 
                    successName="Updated"
                    errorName="Try again"
                    staleOnClick={handleUpdateNonDraft}
                    errorOnClick={handleUpdateNonDraft}
                />
            }
        } else if (variant.status === 'draft') {
            name = "Publish";  
            onClick = openPublisher;
            icon = <CaretDownFill />;
        }

        return <button className="button small main-button" onClick={() => onClick()}>
            <span>{name}</span>{icon}
        </button>
    }*/

    function handleUpdateNonDraft() {
        setIsNonDraftUpdating(true);
        forceSavePost({
            update: {
                content: variant.content_unsaved
            },
            onSave: (p: Post) => {
                setIsNonDraftUpdating(false)
                toast.success(<div>Post Updated. <a className="link" href={p.variants[currentLanguageId].url} target="_blank">View</a></div>, {
                    autoClose: 5000
                })
            }
        });
    }

    function toggleFullscreen() {

        if (!isFullScreen) {
            setIsFullScreen(true);
            window.addEventListener("keyup", checkFullscreenClose)
        } else {
            closeFullscreen();
        }

    }
    function closeFullscreen() {
        window.removeEventListener("keyup", checkFullscreenClose);
        setIsFullScreen(false);
    }
    function checkFullscreenClose(e: KeyboardEvent) {
        if (e.key === "Escape")
            closeFullscreen();
    }

    const isNotDraft =  variant.status !== 'draft';
    const content = isNotDraft ? (variant.content_unsaved || variant.content) : variant.content;

    // have to first click Edit Post to edit published/scheduled posts
    /*const [nonDraftPostEditing, setNonDraftPostEditing] = useState(false);
    const [isUnPublishing, setIsUnPublishing] = useState(false);
    const [isNonDraftUpdating, setIsNonDraftUpdating] = useState(false);*/

    const holdAutoSavingRef = useRef(false); // for setInterval

    useEffect(() => {
        holdAutoSavingRef.current = isPublisherOpen || isUnPublishing || isNonDraftUpdating
    }, [isPublisherOpen, isUnPublishing, isNonDraftUpdating])


    /*function handleContentUpdate(v: string) {
        handlePostVariantValueChange(isNotDraft ? 'content_unsaved' : 'content', v);
    }*/

    /*function handlePostVariantValueChange(key: string, value: any) {
        updatePostVariantValue(key, value, currentLanguageId)
    }
*//*
    function handleCreateVariant(languageId: number) {
        createVariant({languageId, onCreate: () => {
            setCurrentLanguageId(languageId);
        }})
    }*/

    function handleUnPublish() {
        const update = {variants: {[currentLanguageId]: {status: 'draft'}}};  
        forceSavePost({
            update,
            onSave: () => {
                toast("Post unpublished")
            }
        });
        setIsUnPublishing(false);
    }

    useEffect(() => {
        if (loadPostAjax.status === 'success') {
            setNonDraftPostEditing(!!(isNotDraft && variant.content_unsaved))
        }
    }, [loadPostAjax.status])

    /*return <div className={"post-editor" + (isFullScreen ? " fullscreen" : "") } ref={viewRef}>

        {/!*<div className="pos-rel">  this element is required to make the tooltip work correctly
            <div className="post-editor-top">

                <div className="post-editor-top-content">

                    <LanguageSelector
                        languages={languages}
                        variants={variants}
                        currentLanguageId={currentLanguageId}
                        onChange={setCurrentLanguageId}
                        onCreate={handleCreateVariant}
                    />

                    <div className="post-editor-title-row">

                        <div className="title-textarea-wrap">
                            <TextareaAutosize
                                className="post-editor-title"
                                placeholder="Title..."
                                value={variant.title || ""}
                                onChange={(e) => handlePostVariantValueChange('title', e.target.value)}
                            />
                        </div>

                        <div className="status">
                            <span>{variant.status}</span>
                        </div>
                         <div className="status">
                            <span>{variant.status}</span>
                        </div>

                    </div>

                    <div className="post-editor-settings">

                        <div className="post-editor-settings-buttons">
                            <div className="left">
                                <button
                                    className={"button small" + (!isSettingsOpen ? " secondary" : " inactive")}
                                    onClick={isSettingsOpen ? undefined : openSettingsView}
                                >
                                    <span>Settings</span><GearFill />
                                </button>

                                <a href={ getBlogUrl(subdomain, '/p/' + post.preview_id + "/" + currentLanguage.code) } target="_blank">
                                    <button className="button small secondary view" >
                                        <span>View</span><BoxArrowUpRight />
                                    </button>
                                </a>
                                <button
                                    className={"button small" + (!isFullScreen ? " secondary" : " inactive")}
                                    onClick={toggleFullscreen}
                                    data-tip="Toggle Fullscreen"
                                >
                                    <Fullscreen />
                                </button>
                            </div>

                            <div className="publish-buttons">
                                <UnPublishButton />
                                <MainButton />

                                <PostPublisher
                                    id={id}
                                    currentLanguageId={currentLanguageId}
                                    publisherViewRef={publisherViewRef}
                                    isOpen={isPublisherOpen}
                                    closePublisher={closePublisher}
                                />
                            </div>
                        </div>

                        <Settings
                            isSettingsOpen={isSettingsOpen}
                            settingsViewRef={settingsViewRef}
                            id={id}
                            currentLanguageId={currentLanguageId}
                        />

                    </div>

                </div>

            </div>

            <div
                className="post-editor-wrap"
                spellCheck={false}
            >
                <Editor
                    id={id}
                    value={content}
                    currentLanguageId={currentLanguageId}
                    onChange={(v: string) => handleContentUpdate(v)}
                    editable={variant.status === 'draft' || nonDraftPostEditing}
                />
            </div>

            <div
                className="post-editor-bottom"
            >
                <div className="post-editor-bottom-content">
                    <div className="right">
                        {
                            savePostAjax.status === 'loading' ?
                            <span className="saving">Saving...</span> : null
                        }
                        <span className="words" id="pm-word-count"/>
                        <a target="_blank" href="/docs/writing" className="help">
                            <InfoCircle />
                        </a>
                    </div>
                </div>
            </div>

        </div>*!/}
        
        {/!*{
            isUnPublishing ?
            <PopupConfirm 
                title={( variant.status === 'published' ? 'Unpublish' : 'Unschedule' ) + " Post"}
                text={"Are you sure to " + ( variant.status === 'published' ? 'unpublish' : 'unschedule' ) + " this post? It will be changed to a draft."}
                name={( variant.status === 'published' ? 'Unpublish' : 'Unschedule' )}
                onClick={handleUnPublish}
                onCancel={() => setIsUnPublishing(false)}
            /> : null
        }*!/}

        {/!*<Tooltip place="bottom" />*!/}

    </div>*/

}

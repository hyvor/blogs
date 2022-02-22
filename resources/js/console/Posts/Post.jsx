import { useActions, useValues } from 'kea';
import React, { useEffect, useRef, useState } from 'react';
import { BoxArrowUpRight, CaretDownFill, Fullscreen, GearFill, PencilFill, Trash } from 'react-bootstrap-icons';
import Editor from './ProseMirror/Editor';
import TextareaAutosize from 'react-textarea-autosize';
import onOutsideClick from '../../helpers/onOutsideClick';
import postLogic from '../logic/postLogic';
import { getBlogUrl } from '../lib/blog-helpers';

export default function Post( {subdomain, id} ) {

    id = parseInt(id)

    const postLogicInst = postLogic({id});
    const { post, loadPostAjax, savePostAjax, getDiff } = useValues(postLogicInst)
    const { updatePostValue, savePost, deletePost } = useActions(postLogicInst)


    const [isFullScreen, setIsFullScreen] = useState(false);


    /**
     * Disallow outside clicking when the content has changed
     */
    const viewRef = useRef(null);

    // saving
    useEffect(() => {

        // auto save
        const autoSaveInterval = setInterval(savePost, 10000);

        function checkSave(e) {
            if (e.keyCode === 83 && (e.ctrlKey || e.metaKey)) { // ctrl + s
                savePost();
                e.preventDefault();
            }
        }

        function checkSaveUnload() {
            if (
                (post.status === 'published' || post.status === 'scheduled') && 
                Object.keys(getDiff).length > 0
            ) {
                return true;
            } else {
                savePost();
            }
        }

        // save on CTRL + S
        window.addEventListener('keydown', checkSave);
        // save on unload
        window.addEventListener('beforeunload', checkSaveUnload);

        // save on outsideClick
        const removeOutsideEvent = onOutsideClick(viewRef.current, savePost, false, false, false);

        return () => {
            clearInterval(autoSaveInterval)
            window.removeEventListener('keydown', checkSave);
            window.removeEventListener('beforeunload', checkSaveUnload);
            removeOutsideEvent(false);
        }
    }, [id])

    /**
     * Settings view
     * Close on outside click
     */
    const [isSettingsOpen, setIsSettingsOpen] = useState(false);
    const settingsViewRef = useRef(null);

    function openSettingsView() {
        setIsSettingsOpen(true);
        onOutsideClick(settingsViewRef.current, closeSettingsView);
    }
    function closeSettingsView() {
        setIsSettingsOpen(false);
    }

    const [ settingsType, setSettingsType ] = useState('basic'); // basic | advanced

    // have to first click Edit Post to edit published/scheduled posts
    const [publishedPostEditing, setPublishedPostEditing] = useState(false);


    // something changed?
    // null if not
    // object ({old, new}) if changed
    function getDiffWithOld() {
        var diff = {};
        for (var key in posts[id]) {
            if (post[key] !== posts[id][key]) {
                diff[key] = {
                    old: posts[id][key],
                    new: post[key]
                }
            }
        }
        return diff;
    }

    function MainButton() {
        let name, onClick, icon, disabled = false;

        if (post.status === 'published' || post.status === 'scheduled') {
            if (!publishedPostEditing) {
                name = "Edit Post";
                icon = <PencilFill />
                onClick = () => setPublishedPostEditing(true);
            } else {
                const diff = getDiffWithOld();
                if (diff) {
                    const count = Object.keys(diff).length;
                    name = "Save Changes" + (count > 0 ? " (" + count + ")" : "");
                    onClick = () => showUpdateDetails();
                    disabled = count === 0;
                }
            }
        } else if (post.status === 'draft') {
            name = "Publish Post";
            onClick = () => showPublishDetails()
        } else { // deleted
            name = "Recover Post";
            onClick = () => showDeleteDetails()
        }

        return <button className="button small main-button" onClick={() => onClick()}>
            <span>{name}</span>{icon || <CaretDownFill />}
        </button>
    }

    function showUpdateDetails() {
        alert("Updated");
    }

    function handleDelete() {
        deletePost({id})
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
    function checkFullscreenClose(e) {
        if (e.key === "Escape")
            closeFullscreen();
    }

    return <div className={"post-editor" + (isFullScreen ? " fullscreen" : "") } ref={viewRef}>

        <div className="pos-rel"> {/* this element is required to make the tooltip work correctly */}
            <div className="post-editor-top">

                <div className="post-editor-top-content">

                    <div className="post-editor-title-row">

                        <div className="title-textarea-wrap">
                            <TextareaAutosize 
                                className="post-editor-title" 
                                placeholder="Title..."
                                value={post.title || ""}
                                onChange={(e) => updatePostValue('title', e.target.value)}
                            />
                        </div>

                    </div>

                    <div className="post-editor-settings">

                        <div className="post-editor-settings-buttons">
                            <div className="left">
                                <button 
                                    className={"button small" + (!isSettingsOpen ? " secondary" : " inactive")}
                                    onClick={isSettingsOpen ? null : openSettingsView}    
                                >
                                    <span>Settings</span><GearFill />
                                </button>

                                <a href={ getBlogUrl(subdomain, '/p/' + post.preview_id) } target="_blank">
                                    <button className="button small secondary view" >
                                        <span>View</span><BoxArrowUpRight />
                                    </button>
                                </a>
                                <button 
                                    className={"button small" + (!isFullScreen ? " secondary" : " inactive")}
                                    onClick={toggleFullscreen}
                                >
                                    <Fullscreen />
                                </button>
                            </div>

                            <div className="publish-buttons">
                                {
                                    savePostAjax.status === 'loading' ?
                                    <span className="saving">Saving...</span> : null
                                }
                                <MainButton />
                            </div>
                        </div>
                        
                        <div className={"settings-view-wrap " + (isSettingsOpen ? "active" : "inactive") }>
                            <div ref={settingsViewRef} className="post-editor-settings-view">

                                <div className="setting-select">
                                    <span onClick={() => setSettingsType('basic')} className={settingsType === 'basic' ? 'active' : ''}>Basic</span>
                                    <span onClick={() => setSettingsType('advanced')} className={settingsType === 'advanced' ? 'active' : ''}>Advanced</span>
                                </div>

                                {settingsType === 'basic' ?
                                <div className="setting-show">
                                    <div className="post-setting-dual">
                                        <Setting 
                                            title="Slug"
                                            description="The unique part of the URL to identify this post"
                                        >
                                            <input 
                                                className="input" 
                                                value={post.slug}
                                                onChange={(e) => updatePostValue("slug", e.target.value)}
                                            ></input>
                                        </Setting>

                                        <Setting 
                                            title="Publish Time"
                                            className="post-setting-featured-image"
                                        >
                                            <input className="input" value="2021-01-01" onChange={() => {}}></input>
                                        </Setting>

                                    </div>

                                    <div className="post-setting-dual">

                                        <Setting 
                                            title="Authors"
                                            description="The unique part of the URL to identify this post"
                                        >
                                            <input className="input" value="Ishini Avindya" onChange={() => {}}></input>
                                        </Setting>

                                        <Setting 
                                            title="Tags"
                                            className="post-setting-featured-image"
                                        >
                                            <input className="input" value="#creative" onChange={() => {}}></input>
                                        </Setting>

                                    </div>

                                    <div className="post-setting-dual">

                                        <Setting 
                                            title="Description"
                                            description="Summarization of the post for listing pages and search engines."
                                            className="post-setting-description"
                                        >
                                            <textarea 
                                                className="input"
                                                placeholder="Write a description..."
                                                value={post.description}
                                                onChange={e => updatePostValue('description', e.target.value)}
                                            ></textarea>
                                        </Setting>

                                        <Setting 
                                            title="Featured Image"
                                            className="post-setting-featured-image"
                                        >
                                            <div className="image-uploader">Upload a file</div>
                                        </Setting>

                                    </div>

                                    <div className="post-setting-dual">

                                        <Setting 
                                            title="Featured?"
                                            description="The unique part of the URL to identify this post"
                                        >
                                            <input type="checkbox"></input>
                                        </Setting>

                                        <Setting 
                                            title="Delete Post"
                                        >
                                            <button 
                                                className="button small danger"
                                                onClick={handleDelete}
                                            >Delete <Trash /></button>
                                        </Setting>

                                    </div>
                                </div>
                                : 
                                <div className="setting-show">

                                    <Setting 
                                        title="Canonical URL"
                                        description=""
                                    >
                                        <input 
                                            className="input"
                                            value={post.canonical_url}
                                            onChange={e => updatePostValue('canonical_url', e.target.value)}
                                        ></input>
                                    </Setting>

                                    <div className="post-setting-dual">

                                        <Setting 
                                            title="Header HTML Code"
                                            description="Summarization of the post for listing pages and search engines."
                                            className="post-setting-description"
                                        >
                                            <textarea 
                                                className="input"
                                                placeholder="Paste HTML code..."
                                                value={post.code_head}
                                                onChange={e => updatePostValue('code_head', e.target.value)}
                                            ></textarea>
                                        </Setting>

                                        <Setting 
                                            title="Footer HTML Code"
                                            description="Summarization of the post for listing pages and search engines."
                                            className="post-setting-description"
                                        >
                                            <textarea 
                                                className="input" 
                                                placeholder="Paste HTML code..."
                                                value={post.code_foot}
                                                onChange={e => updatePostValue('code_foot', e.target.value)}
                                            ></textarea>
                                        </Setting>

                                    </div> 
                                </div>
                                }
                            </div>  
                        </div>

                    </div>
                
                </div>
                
            </div>

            <div 
                className="post-editor-wrap"
                spellCheck={false}
                onClick={() => false && view && view.focus()}
            >
                {
                    loadPostAjax.status === 'loading' ? null :
                    <Editor 
                        id={id}
                        value={post.content}
                        onChange={v => updatePostValue('content', v)}
                    />
                }
            </div>

            <div
                className="post-editor-bottom"
            >
                <div className="post-editor-bottom-content">
                    <div id="pm-navigator-wrap"></div>
                    <div className="right">
                        <span className="words" id="pm-word-count"></span>
                    </div>
                </div>
            </div>

        </div>
        

    </div>

}   

function Setting(props) {

    return <div className={"post-setting " + (props.className || "")}>
        <div className="post-setting-title">{props.title}</div>
        { false ? <div className="post-setting-description">{props.description}</div> : null }

        <div className="post-setting-content">
            {props.children}
        </div>
    </div>

}
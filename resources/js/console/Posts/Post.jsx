import { useActions, useValues } from 'kea';
import React, { useEffect, useRef, useState } from 'react';
import { BoxArrowUpRight, CaretDownFill, GearFill, PencilFill, Trash } from 'react-bootstrap-icons';
import postsLogic from '../logic/postsLogic';
import Editor from './ProseMirror/Editor';
import TextareaAutosize from 'react-textarea-autosize';
import onOutsideClick from '../../helpers/onOutsideClick';
import postLogic from '../logic/postLogic';

export default function Post( {subdomain, id} ) {

    id = parseInt(id)

    const postSubdomainLogic = postsLogic({subdomain});

    const { posts } = useValues(postSubdomainLogic)
    const { deletePost } = useActions(postSubdomainLogic)

    const postLogicInst = postLogic({id});
    const { post } = useValues(postLogicInst)
    const { updatePostValue } = useActions(postLogicInst)


    /**
     * Disallow outside clicking when the content has changed
     */
    const viewRef = useRef(null);
    useEffect(() => {
        /* const remove = onOutsideClick(viewRef.current, (e) => {
            // e.preventDefault();
        }, false, false);
        return remove; */
    }, [])

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
    function getDiff() {
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
                const diff = getDiff();
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

        return <button className="button small" onClick={() => onClick()}>
            <span>{name}</span>{icon || <CaretDownFill />}
        </button>
    }

    function showUpdateDetails() {
        alert("Updated");
    }

    function handleDelete() {
        deletePost({id})
    }

    return <div className="post-editor" ref={viewRef}>

        <div className="post-editor-top">

            <div className="post-editor-title-row">

                <div className="title-textarea-wrap">
                    <TextareaAutosize 
                        className="post-editor-title" 
                        placeholder="Title..."
                        value={post.title}
                        onChange={(e) => updatePostValue('title', e.target.value)}
                    />
                </div>

            </div>

            <div className="post-editor-settings">

                <div className="post-editor-settings-buttons">
                    <div className="left">
                        <button 
                            className={"button small" + (!isSettingsOpen ? " inactive" : "")}
                            onClick={isSettingsOpen ? null : openSettingsView}    
                        >
                            <span>Settings</span><GearFill />
                        </button>

                        <a href="/se" target="_blank">
                            <button className="button small inactive view" >
                                <span>View</span><BoxArrowUpRight />
                            </button>
                        </a>
                    </div>

                    <div className="publish-buttons">
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

        {
            post.content ?
            <Editor 
                id={id}
                value={post.content}
                onChange={v => updatePostValue('content', v)}
            /> : null }

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
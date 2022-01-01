import { useValues } from 'kea';
import React, { useEffect, useRef, useState } from 'react';
import { BoxArrowUpRight, CaretDownFill, GearFill, Trash } from 'react-bootstrap-icons';
import postsLogic from '../logic/postsLogic';
import Editor from './ProseMirror/Editor';
import TextareaAutosize from 'react-textarea-autosize';
import onOutsideClick from '../../helpers/onOutsideClick';

export default function Post( {subdomain, id} ) {


    const { posts } = useValues(postsLogic({subdomain}))
    const [ post, setPost ] = useState({});

    useEffect(() => {
        setPost(posts[id]);
    }, [id]);

    function handleDataChange(key, val) {
        setPost({...post, ...{[key]: val}});
    }

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

    return <div className="post-editor" ref={viewRef}>

        <div className="post-editor-top">

            {/* <div className="cover-image" style={{
                backgroundImage: 'url("https://picsum.photos/400/300")',
            }}></div> */}

            <div className="post-editor-title-row">

                <div className="title-textarea-wrap">
                    <TextareaAutosize 
                        className="post-editor-title" 
                        placeholder="Title..."
                        value={post.title}
                        onChange={(e) => handleDataChange('title', e.target.value)}
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
                        <button className="button small"><span>Publish</span><CaretDownFill /></button>
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
                                        onChange={(e) => handleDataChange("slug", e.target.value)}
                                    ></input>
                                </Setting>

                                <Setting 
                                    title="Publish Time"
                                    className="post-setting-featured-image"
                                >
                                    <input className="input" value="2021-01-01"></input>
                                </Setting>

                            </div>

                            <div className="post-setting-dual">

                                <Setting 
                                    title="Authors"
                                    description="The unique part of the URL to identify this post"
                                >
                                    <input className="input" value="Ishini Avindya"></input>
                                </Setting>

                                <Setting 
                                    title="Tags"
                                    className="post-setting-featured-image"
                                >
                                    <input className="input" value="#creative"></input>
                                </Setting>

                            </div>

                            <div className="post-setting-dual">

                                <Setting 
                                    title="Description"
                                    description="Summarization of the post for listing pages and search engines."
                                    className="post-setting-description"
                                >
                                    <textarea className="input" placeholder="Write a description..."></textarea>
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
                                    <button className="button small danger">Delete <Trash /></button>
                                </Setting>

                            </div>
                        </div>
                        : 
                        <div className="setting-show">

                            <Setting 
                                title="Canonical URL"
                                description=""
                            >
                                <input className="input"></input>
                            </Setting>

                            <div className="post-setting-dual">

                                <Setting 
                                    title="Header HTML Code"
                                    description="Summarization of the post for listing pages and search engines."
                                    className="post-setting-description"
                                >
                                    <textarea className="input" placeholder="Paste HTML code..."></textarea>
                                </Setting>

                                <Setting 
                                    title="Footer HTML Code"
                                    description="Summarization of the post for listing pages and search engines."
                                    className="post-setting-description"
                                >
                                    <textarea className="input" placeholder="Paste HTML code..."></textarea>
                                </Setting>

                            </div> 
                        </div>
                        }
                    </div>  
                </div>


            </div>
        </div>

        <Editor />

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
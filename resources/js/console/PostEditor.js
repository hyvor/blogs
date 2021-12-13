import React, { useEffect, useRef, useState } from 'react';
import { GearFill } from 'react-bootstrap-icons';
import Editor from './ProseMirror/Editor';

export default function PostEditor() {

    const [isSettingsOpen, setIsSettingsOpen] = useState(false);

    const [heading, setHeading] = useState('');
    const [slug, setSlug] = useState('');

    return <div className="post-editor">

        <div className="post-editor-top">
            <div 
                className="post-editor-heading" 
                placeholder="Title..." 
                contentEditable={true}
            >{heading}</div>

            <div className="post-editor-settings">
                <button 
                    className={"button small" + (!isSettingsOpen ? " inactive" : "")}
                    onClick={() => setIsSettingsOpen(!isSettingsOpen)}    
                >
                    <span>Settings</span>
                    &nbsp;
                    <GearFill />
                </button>
                {

                    isSettingsOpen ?
                    <div className="post-editor-settings-view">

                        <div className="post-setting-dual">

                            <Setting 
                                title="Slug"
                                description="The unique part of the URL to identify this post"
                            >
                                <input className="input" value="hello-world"></input>
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

                        <Setting 
                            title="Canonical URL"
                            description=""
                        >
                            <input className="input"></input>
                        </Setting>
                    </div>  
                    : null
                }
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
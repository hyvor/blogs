import React, { ReactNode } from "react";
import { usePostActions, usePostValues } from "../helpers";


export default function Settings({id}: {id: number}) {

    const { post, editorState, currentLanguage, currentVariant } = usePostValues(id);
    const {
        updatePostValue, updateCurrentPostVariantValue,
        deletePost, deleteVariant,
        savePost,
        changeEditorState
    } = usePostActions(id);

    return <div className="post-settings-wrap">

        <Setting title="Slug">
            <input
                className="input"
                value={currentVariant.slug || ''}
                onChange={(e) => updateCurrentPostVariantValue("slug", e.target.value)}
                maxLength={250}
            />
        </Setting>

        <Setting 
            title="Description"
            description="Summarization of the post for listing pages and search engines."
            className="post-setting-description"
        >
            <textarea
                className="input description-input"
                placeholder="Write a description..."
                value={currentVariant.description || ''}
                onChange={e => updateCurrentPostVariantValue('description', e.target.value)}
                maxLength={350}
            />
        </Setting>

    </div>

}

type SettingProps = {
    title: string,
    description?: string,
    className?: string,
    children: ReactNode
}

const Setting: React.FC<SettingProps> = ({ title, description, className, children }) => (

    <div className={"post-setting " + (className || "")}>

        <div className="post-setting-left">
            <div className="post-setting-title">{title}</div>
            <div style={{display: "none"}} className="post-setting-description">{description}</div>
        </div>

        <div className="post-setting-content">
            {children}
        </div>
    </div>

);
import PostLanguageSelector from "../PostLanguageSelector";
import React from "react";
import TitleRow from "./TitleRow";
import SettingsRow from "./SettingsRow";


export default function PostTop({ id }: { id: number }) {

    return <div className="post-editor-top">

        <div className="post-editor-top-content">
            <SettingsRow id={id} />
        </div>

    </div>

}
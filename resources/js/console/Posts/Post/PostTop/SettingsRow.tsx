import { BoxArrowUpRight, Fullscreen, GearFill } from "react-bootstrap-icons";
import { getBlogUrl } from "../../../lib/blog-helpers";
import React, { useEffect } from "react";
import { usePostActions, usePostValues } from "../helpers";
import getSubdomain from "../../../logic-helpers/subdomain";
import UnpublishButton from "./UnpublishButton";
import MainButton from "./MainButton";
import Publisher from "./Publisher";
import PostSettings from "./PostSettings";

export default function SettingsRow({ id }: { id: number }) {

    const { post, editorState, currentLanguage } = usePostValues(id);
    const { changeEditorState } = usePostActions(id)

    function checkFullscreenClose(e: KeyboardEvent) {
        if (e.key === "Escape")
            changeEditorState('isFullscreen', false);
    }

    useEffect(() => {
        if (editorState.isFullscreen) {
            window.addEventListener("keyup", checkFullscreenClose)
            return () => {
                window.removeEventListener("keyup", checkFullscreenClose);
            }
        }
    }, [editorState.isFullscreen])

    return <div className="post-editor-settings">

        <div className="post-editor-settings-buttons">
            <div className="left">
                <button
                    className={"button small" + (!editorState.isChangingSettings ? " secondary" : " inactive")}
                    onClick={() => changeEditorState('isChangingSettings', true)}
                >
                    <span>Settings</span><GearFill />
                </button>

                <a
                    href={getBlogUrl(getSubdomain(), '/p/' + post.preview_id + "/" + currentLanguage.code)}
                    target="_blank"
                >
                    <button className="button small secondary view" >
                        <span>View</span><BoxArrowUpRight />
                    </button>
                </a>

            </div>

            <div className="publish-buttons">
                <UnpublishButton id={id} />
                <MainButton id={id} />

                <Publisher id={id} />
            </div>
        </div>

        <PostSettings id={id} />

    </div>

}
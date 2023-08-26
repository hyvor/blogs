import React, { ReactNode } from "react";
import { ExclamationCircleFill, Gear, Link45deg, Magic, SearchHeart, XCircleFill } from "react-bootstrap-icons";
import Settings from "./Settings";
import Seo, { SeoScoreTag } from "./Seo/Seo";
import { usePostActions, usePostValues } from "../helpers";
import Ai from "./Ai/Ai";
import LinksComponent from "./Links/LinksComponent";
import { useUpdateLinkAnalysis } from "./Links/links";
import Loader from "../../../ReusableComponents/Loader";
import { PostSettingsSection } from "../../../states";

export default function PostRight({id} : {id: number}) {

    type SectionType = 'Settings' | 'SEO' | 'Links' | 'AI';

    const { currentVariantSeoResults, editorState } = usePostValues(id);
    const { changeEditorState } = usePostActions(id);

    const ToolbarButton = (
        {icon, text, children, name} : 
        {icon: ReactNode, text: SectionType, children?: ReactNode, name: PostSettingsSection}
    ) => {

        return <button
            className={editorState.settingsSection === name ? 'active' : ''}
            onClick={() => changeEditorState('settingsSection', name)}
        >
            <span className="icon">{icon}</span>
            <span className="text">{children || text}</span>
        </button>

    }

    const linkAnalysisProps = useUpdateLinkAnalysis(id);

    const LinksTag = () => {

        const counts = linkAnalysisProps.counts;

        if (counts.loading > 0) {
            return <Loader inline={true} size="mini" />
        }

        if (counts.broken > 0) {
            return <span className="global-seo-score-tag link-tag red">{counts.broken} <XCircleFill /></span>
        }

        if (counts.redirect > 0) {
            return <span className="global-seo-score-tag link-tag orange">{counts.redirect} <ExclamationCircleFill /></span>
        }

        return null;

    }

    return <div className="post-right">


        <div className="toolbar">
            <ToolbarButton icon={<Gear />} text="Settings" name="settings" />
            <ToolbarButton icon={<SearchHeart />} text="SEO" name="seo">
                SEO {
                    <span className="seo-score">
                        <SeoScoreTag 
                            score={Math.round(currentVariantSeoResults.average)}
                            percentage={true}
                        />
                    </span>
                }
            </ToolbarButton>
            <ToolbarButton icon={<Link45deg />} text="Links" name="links">
                Links <span className="seo-score">
                    <LinksTag />
                </span>
            </ToolbarButton>
            <ToolbarButton icon={<Magic />} text="AI" name="ai" />
        </div>

        {editorState.settingsSection === 'settings' && <Settings id={id} />}
        {editorState.settingsSection === 'seo' && <Seo id={id} />}
        {editorState.settingsSection === 'links' && <LinksComponent id={id} linkAnalysisProps={linkAnalysisProps} />}
        {editorState.settingsSection === 'ai' && <Ai id={id} />}

    </div>

}
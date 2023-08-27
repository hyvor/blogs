import React, { ReactNode } from "react";
import { BoxArrowUpRight, ExclamationCircleFill, Gear, Link, Link45deg, Magic, SearchHeart, XCircleFill } from "react-bootstrap-icons";
import Settings from "./Settings";
import Seo, { SeoScoreTag } from "./Seo/Seo";
import { usePostValues } from "../helpers";
import Ai from "./Ai/Ai";
import LinksComponent from "./Links/LinksComponent";
import { useUpdateLinkAnalysis } from "./Links/links";
import Loader from "../../../ReusableComponents/Loader";

export default function PostRight({id} : {id: number}) {

    type SectionType = 'Settings' | 'SEO' | 'Links' | 'AI';

    const { currentVariantSeoResults, currentVariantLinkAnalysis } = usePostValues(id);
 
    const [section, setSection] = React.useState<SectionType>('Settings');

    const ToolbarButton = ({icon, text, children} : {icon: ReactNode, text: SectionType, children?: ReactNode}) => {

        return <button
            className={section === text ? 'active' : ''}
            onClick={() => setSection(text)}
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
            <ToolbarButton icon={<Gear />} text="Settings" />
            <ToolbarButton icon={<SearchHeart />} text="SEO">
                SEO {
                    <span className="seo-score">
                        <SeoScoreTag 
                            score={Math.round(currentVariantSeoResults.average)}
                            percentage={true}
                        />
                    </span>
                }
            </ToolbarButton>
            <ToolbarButton icon={<Link45deg />} text="Links">
                Links <span className="seo-score">
                    <LinksTag />
                </span>
            </ToolbarButton>
            <ToolbarButton icon={<Magic />} text="AI" />
        </div>

        {section === 'Settings' && <Settings id={id} />}
        {section === 'SEO' && <Seo id={id} />}
        {section === 'Links' && <LinksComponent id={id} linkAnalysisProps={linkAnalysisProps} />}
        {section === 'AI' && <Ai id={id} />}

    </div>

}
import React, { ReactNode } from "react";
import { BoxArrowUpRight, Gear, Link, Link45deg, Magic, SearchHeart } from "react-bootstrap-icons";
import Settings from "./Settings";
import Seo, { SeoScoreTag } from "./Seo/Seo";
import { usePostValues } from "../helpers";
import Ai from "./Ai/Ai";
import LinksComponent from "./Links/LinksComponent";

export default function PostRight({id} : {id: number}) {

    type SectionType = 'Settings' | 'SEO' | 'Links' | 'AI';

    const { currentVariantSeoResults } = usePostValues(id);
 
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
            <ToolbarButton icon={<Link45deg />} text="Links" />
            <ToolbarButton icon={<Magic />} text="AI" />
        </div>

        {section === 'Settings' && <Settings id={id} />}
        {section === 'SEO' && <Seo id={id} />}
        {section === 'Links' && <LinksComponent id={id} />}
        {section === 'AI' && <Ai id={id} />}

    </div>

}
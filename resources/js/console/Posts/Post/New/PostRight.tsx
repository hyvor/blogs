import React, { ReactNode } from "react";
import { BoxArrowUpRight, Gear, Link, Link45deg, Magic, SearchHeart } from "react-bootstrap-icons";
import Settings from "./Settings";
import Seo from "./Seo/Seo";

export default function PostRight({id} : {id: number}) {

    type SectionType = 'Settings' | 'SEO' | 'Links' | 'AI';

    const [section, setSection] = React.useState<SectionType>('SEO');

    const ToolbarButton = ({icon, text} : {icon: ReactNode, text: SectionType}) => {

        return <button
            className={section === text ? 'active' : ''}
            onClick={() => setSection(text)}
        >
            <span className="icon">{icon}</span>
            <span className="text">{text}</span>
        </button>

    }

    return <div className="post-right">


        <div className="toolbar">
            <ToolbarButton icon={<Gear />} text="Settings" />
            <ToolbarButton icon={<SearchHeart />} text="SEO" />
            <ToolbarButton icon={<Link45deg />} text="Links" />
            <ToolbarButton icon={<Magic />} text="AI" />
        </div>

        {section === 'Settings' && <Settings id={id} />}
        {section === 'SEO' && <Seo id={id} />}

    </div>

}
import React, { ReactNode } from "react";
import { BoxArrowUpRight, Gear, Magic, SearchHeart } from "react-bootstrap-icons";
import Settings from "./Settings";

export default function PostRight({id} : {id: number}) {

    type SectionType = 'Settings' | 'SEO' | 'AI';

    const [section, setSection] = React.useState<SectionType>('Settings');

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
            <ToolbarButton icon={<Magic />} text="AI" />
        </div>

        <div className="toolbar-content">

            {section === 'Settings' && <Settings id={id} />}


        </div>

    </div>

}
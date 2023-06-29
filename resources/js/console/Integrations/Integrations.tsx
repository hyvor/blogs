import React, {ReactNode} from "react";
import HyvorTalk from "./HyvorTalk/HyvorTalk";
import NavLink from "../ReusableComponents/NavLink";
import getSubdomain from "../logic-helpers/subdomain";
import './Integrations.scss';

export default function Integrations({type} : {type?: string}) {

    const subdomain = getSubdomain();
    let Type = null;

    if (type === 'hyvor-talk') {
        Type = HyvorTalk;
    }

    function IntegrationLink(
        {path, name, imageName} :
        {
            path: string,
            name: string,
            imageName: string,
        }
    ) {
        const integrationsPrefix = `/console/${subdomain}/integrations`;
        const imageUrl = '/img/third-party/' + imageName;

        return <NavLink
            href={integrationsPrefix + path}
            exact={1}
            className="integration-nav-link"
        >
            <img src={imageUrl} alt={name} />
            <span>{name}</span>
        </NavLink>
    }

    return <div className="posts-view settings-view integrations-view">
        <div className="box box-left">
            <div className="settings-nav">
                <IntegrationLink
                    path="/hyvor-talk"
                    name="Hyvor Talk"
                    imageName="hyvor-talk.svg"
                />
            </div>
        </div>
        <div className="box box-right settings-right">
            { Type ? <Type /> : null }
        </div>
    </div>

}
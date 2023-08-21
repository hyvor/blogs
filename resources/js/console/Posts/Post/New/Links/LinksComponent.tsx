import React from "react";
import { usePostValues } from "../../helpers";
import { getLinksFromContent } from "./links";
import { useUserBlog } from "../../../../logic-helpers/blog";
import { ArrowClockwise, CheckCircleFill, Eye, EyeFill, X, XCircle } from "react-bootstrap-icons";
import Tooltip from "../../../../ReusableComponents/Tooltip";

export default function LinksComponent({id} : {id: number}) {

    const { blog: {base_url: baseUrl} } = useUserBlog();
    const { currentVariant } = usePostValues(id);
    const content = currentVariant.content_unsaved || currentVariant.content;

    const links = getLinksFromContent(content, baseUrl);

    return <div className="toolbar-content links-analysis">

        <div className="links">

            {

                links.map(link => {

                    return <div className="link-wrap" key={link.index}>

                        <div className="link-name">

                            <div className="link-anchor">
                                {link.anchor}
                            </div>

                            <div className="link-url">
                                <a 
                                    href={link.href} 
                                    target="_blank" 
                                    rel="nofollow"
                                >
                                    {link.href}
                                </a>
                            </div>

                            <div className="link-type-wrap">
                                <span className="link-type">{link.type}</span>
                            </div>


                        </div>

                        {/* <div className="link-type-wrap">
                        </div> */}

                        <div className="link-status">
                            <Tooltip tooltip="HTTP status 200">
                                <span className="link-status-tag">
                                    <span className="status">200</span>
                                    <span className="icon"><CheckCircleFill /></span>
                                </span>
                            </Tooltip>
                        </div>

                        <div className="link-buttons">

                            <Tooltip tooltip="Show in Editor">
                                <button className="icon-button">
                                    <EyeFill />
                                </button>
                            </Tooltip>

                            <Tooltip tooltip="Recheck">
                                <button className="icon-button">
                                    <ArrowClockwise />
                                </button>
                            </Tooltip>

                            <Tooltip tooltip="Ignore">
                                <button className="icon-button">
                                    <X />
                                </button>
                            </Tooltip>

                        </div>

                    </div>

                })

            }

        </div>

        {/* <div className="no-results-wrap">
            <NoResults 
                imageWidth={125}
                text="No links found in this post"
            />
        </div> */}
    
    </div>

}
import React, { useState } from "react";
import { usePostValues } from "../../helpers";
import { Link, focusLinkInEditor, getLinksFromContent, getStatusType, isHttpLink, useUpdateLinkAnalysis } from "./links";
import { useUserBlog } from "../../../../logic-helpers/blog";
import { ArrowClockwise, CheckCircleFill, ExclamationCircleFill, EyeSlashFill, PencilFill, XCircleFill } from "react-bootstrap-icons";
import Tooltip from "../../../../ReusableComponents/Tooltip";
import Loader from "../../../../ReusableComponents/Loader";
import { EditorView } from "prosemirror-view";
import Button from "../../../../ReusableComponents/Button";
import NoResults from "../../../../ReusableComponents/NoResults";
import UpgradeRequired from "../../../../ReusableComponents/UpgradeRequired";

export default function LinksComponent(
    {id, linkAnalysisProps} : 
    {id: number, linkAnalysisProps: ReturnType<typeof useUpdateLinkAnalysis>}
) {

    const { currentVariantLinkAnalysis, currentVariantLinks, editorState } = usePostValues(id);

    const links = currentVariantLinks;
    let linksCount = links.length;

    const {
        ok: okCount,
        redirect: redirectCount,
        broken: brokenCount,
        ignored: ignoreCount
    } = linkAnalysisProps.counts;

    const [isReloadingAll, setIsReloadingAll] = useState(false);

    function handleReloadAll() {
        setIsReloadingAll(true);
        linkAnalysisProps.reloadAllLinks(() => {
            setIsReloadingAll(false);
        });
    }

    return <UpgradeRequired 
        minPlan="growth" 
        trialAllowed={true}
        text={
            <div>
                Link Analysis is only available on the <b>Growth plan</b> and above. Upgrade now to analyze links in your posts and say goodbye to broken links.
            </div>
        }
    >    
    
        <div className="toolbar-content links-analysis">

            <div className="header">

                <div className="title">
                    <div className="title-left">
                        Links ({linksCount})
                    </div>
                    <div className="title-right">
                        {
                            linksCount > 0 &&
                            <Button 
                                type="primary" 
                                size="mini" 
                                onClick={handleReloadAll}
                            >
                                {
                                    isReloadingAll ?
                                    <Loader inline={true} size="mini" color="white" /> :
                                    <ArrowClockwise />
                                } Recheck All
                            </Button>
                        }
                    </div>
                </div>

                <div className="summary">

                    {
                        okCount > 0 &&
                        <span className="ok">
                            <span className="number">{okCount}</span> OK <CheckCircleFill />
                        </span>
                    }

                    {
                        brokenCount > 0 &&
                        <span className="broken">
                            <span className="number">{brokenCount}</span> Broken <XCircleFill />
                        </span>
                    }

                    { 
                        redirectCount > 0 &&
                        <span className="redirect">
                            <span className="number">{redirectCount}</span> Redirect <ExclamationCircleFill />
                        </span>
                    }

                    {
                        ignoreCount > 0 &&
                        <span className="ignored">
                            <span className="number">{ignoreCount}</span> Ignored <EyeSlashFill />
                        </span>
                    }

                </div>

            </div>

            <div className="links">

                {

                    links.length ?

                        links.map(link => <LinkComponent
                            key={link.index} 
                            link={link} 
                            status={currentVariantLinkAnalysis[link.originalHref]}
                            editorView={editorState.editorView!}
                            linkAnalysisProps={linkAnalysisProps}
                        />) :

                        <NoResults
                            imageWidth={100}
                            text="No links found in this post"
                            padding={50}
                        />

                }

            </div>
        
        </div>

    </UpgradeRequired>

}

function LinkComponent(
    {link, status, editorView, linkAnalysisProps} :
    {
        link: Link,
        status: number,
        editorView: EditorView,
        linkAnalysisProps: ReturnType<typeof useUpdateLinkAnalysis>
    }
) {

    const [isReloading, setIsReloading] = useState(false);

    const statusType = getStatusType(status);
    const isHttp = isHttpLink(link);

    function handleReload() {
        setIsReloading(true);
        linkAnalysisProps.reloadLink(link, _ => {
            setIsReloading(false);
        });
    }

    function handleIgnoreLink() {
        setIsReloading(true);
        linkAnalysisProps.ignoreLink(link, statusType === "ignored" ? false : true)
            .then(() => {
                setIsReloading(false);
            });
    }

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
                    {link.originalHref}
                </a>
            </div>

            <div className="link-type-wrap">
                <span className="link-type">{link.type}</span>
            </div>

        </div>

        <div className="link-status">
            {
                statusType !== 'loading' && !isReloading ?
                <LinkStatusTag status={status} isAnchor={link.type === 'anchor'} /> :
                <Loader inline={true} size="mini" />
            }
        </div>

        <div className="link-buttons">

            <Tooltip tooltip="Edit in Editor">
                <button 
                    className="icon-button"
                    onClick={() => focusLinkInEditor(link, editorView!)}
                    style={{fontSize: "0.8em"}}
                >
                    <PencilFill />
                </button>
            </Tooltip>

            <Tooltip tooltip="Recheck" className={isHttp ? undefined : "disabled"}>
                <button 
                    className="icon-button"
                    onClick={handleReload}
                >
                    <ArrowClockwise />
                </button>
            </Tooltip>

            <Tooltip tooltip="Ignore this link" className={isHttp ? undefined : "disabled"}>
                <button 
                    className={"icon-button" + (statusType === "ignored" && isHttp ? " active" : "")}
                    onClick={handleIgnoreLink}
                >
                    <EyeSlashFill />
                </button>
            </Tooltip>

        </div>

    </div>

}


export function LinkStatusTag(
    {status, isAnchor = false, showTooltip = true} : 
    {status: number, isAnchor?: boolean, showTooltip?: boolean}) 
{

    const statusType = getStatusType(status);
    let statusDisplay = "";
    let tooltip = "";

    if (statusType === "ok") {
        statusDisplay = "OK";
        tooltip = isAnchor ?
            'Heading ID found' :
            "OK - HTTP status " + status;
    } else if (statusType === "redirect") {
        statusDisplay = "Redirect";
        tooltip = "Redirect status " + status;
    } else if (statusType === "broken") {
        statusDisplay = "Broken";
        tooltip = isAnchor ? 
            'Heading ID not found' :
            "HTTP status " + status;
    } else if (statusType === 'ignored') {
        statusDisplay = "Ignored";
        tooltip = "Link Ignored";
    } else if (statusType === 'error') {
        statusDisplay = "Error";
        tooltip = "Error (on our side)";
    }


    return <Tooltip tooltip={showTooltip ? tooltip : null}>
        <span className={`global-link-status-tag ${statusType}`}>
            <span className="status">{statusDisplay}</span>
            <span className="icon">

                {
                    statusType === "ok" &&
                    <CheckCircleFill />
                }

                {
                    statusType === "redirect" &&
                    <ExclamationCircleFill />
                }

                {
                    statusType === "broken" &&
                    <XCircleFill />
                }

                {
                    statusType === "ignored" &&
                    <EyeSlashFill />
                }

            </span>
        </span>
    </Tooltip>

}
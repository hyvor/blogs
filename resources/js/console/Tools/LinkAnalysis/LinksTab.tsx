import { Fragment, useEffect, useState } from "react";
import { Stats } from "./LinkAnalysisTool";
import getSubdomain from "../../logic-helpers/subdomain";
import { LinkAnalysisLink } from "../../types";
import { useLanguagesValues } from "../../Settings/Languages/helpers";
import api from "../../lib/api";
import React from "react";
import Button from "../../ReusableComponents/Button";
import { Table, TableHead, TableHeadItem, TableRow, TableRowItem } from "../../ReusableComponents/Table";
import NoResults from "../../ReusableComponents/NoResults";
import Loader from "../../ReusableComponents/Loader";
import TableLoadMore from "../../ReusableComponents/TableLoadMore";
import { LinkStatusTag } from "../../Posts/Post/New/Links/LinksComponent";
import Tooltip from "../../ReusableComponents/Tooltip";
import { ArrowClockwise, EyeSlashFill, PencilFill } from "react-bootstrap-icons";
import { callIgnoreLink, callLinkAnalysisApi } from "../../Posts/Post/New/Links/links";

export default function LinksTab({stats} : {stats: null | Stats}) {
    
    type FilterType = null | 'ok' | 'broken' | 'redirect' | 'ignored';
    const [type, setType] = useState<FilterType>(null);

    const subdomain = getSubdomain();
    const [isLoading, setIsLoading] = useState(true);
    const [links, setLinks] = useState<LinkAnalysisLink[]>([]);

    const [hasMore, setHasMore] = useState(false);
    const [isLoadingMore, setIsLoadingMore] = useState(false);

    const limit = 40;

    function loadLinks(more: boolean = false) {

        if (more) {
            setIsLoadingMore(true);
        } else {
            setIsLoading(true);
        }

        api.get<LinkAnalysisLink[]>(subdomain, '/link-analysis/links', {
            limit,
            offset: more ? links.length : 0,
            type
        })
            .then(res => {
                setIsLoading(false);
                setIsLoadingMore(false);
                setLinks(more ? [...links, ...res] : res);
                setHasMore(res.length === limit);
            });

    }

    useEffect(() => {
        loadLinks();
    }, [type]);

    function TypeSelectButton({type: currentType,name}: {type: FilterType, name: string}) {

        return <Button
            type={type === currentType ? 'primary' : 'light'}
            size="small"
            onClick={() => setType(currentType)}
        >
            <span>{name}</span>
            {
                stats && <span className='filter-count'>
                    &nbsp;(
                        {
                            currentType ?
                            stats.counts[currentType] :
                            Object.values(stats.counts).reduce((a, b) => a + b, 0)
                        }
                    )
                </span>
            }
        </Button>
    }

    return <div className='links-tab'>

        <div className='type-selector'>

            <TypeSelectButton type={null} name="All" />
            <TypeSelectButton type="ok" name="OK" />
            <TypeSelectButton type="broken" name="Broken" />
            <TypeSelectButton type="redirect" name="Redirect" />
            <TypeSelectButton type="ignored" name="Ignored" />

        </div>

        {

            isLoading ?

            <Loader padding={60} /> :


            links.length === 0 ?

            <NoResults text="No links analyzed" /> :


            <div>

                <Table columns='1fr 2fr 100px 100px'>

                    <TableHead>
                        <TableHeadItem>Post</TableHeadItem>
                        <TableHeadItem>Link</TableHeadItem>
                        <TableHeadItem>Status</TableHeadItem>
                        <TableHeadItem>Actions</TableHeadItem>
                    </TableHead>

                    <Fragment>
                        {
                            links.map(link => <Link 
                                key={link.id}
                                link={link}
                                links={links}
                                setLinks={setLinks}
                            />)
                        }
                    </Fragment>

                </Table>

                <TableLoadMore
                    isLoading={isLoadingMore}
                    hasMore={hasMore}
                    onClick={() => {
                        loadLinks(true);
                    }}
                />

            </div>

        }

    </div>

}

function Link({link, links, setLinks} : {link: LinkAnalysisLink, links: LinkAnalysisLink[], setLinks: Function}) {


    const { getLanguageById, languages } = useLanguagesValues(); 
    const subdomain = getSubdomain();

    const language = getLanguageById(link.post_variant_language_id);
    const postEditUrl = `/console/${subdomain}/posts/${link.post_id}`;

    const [isRechecking, setIsRechecking] = useState(false);

    function updateLink(n: Partial<LinkAnalysisLink>) {
        setLinks(links.map(l => {
            return l.id === link.id ? {...link, ...n} : l;
        }));
    }

    function handleRecheck() {
        setIsRechecking(true);
        callLinkAnalysisApi(link.post_variant_id, [link.url])
            .then(res => {
                updateLink(res[0]);
            })
            .finally(() => {
                setIsRechecking(false);
            });
    }

    function handleIgnore() {
        const newIgnore = !link.ignored;
        updateLink({ignored: !link.ignored});
        callIgnoreLink(
            link.post_variant_id,
            link.url,
            newIgnore
        );
    }

    return <TableRow key={link.id}>

        <TableRowItem>
            <div>
                {link.post_variant_title || '(No title)'}

                {
                    languages.length > 1 &&
                    language &&
                    <span className='global-lang-tag'>
                        {language.code}
                    </span>
                }
            </div>
        </TableRowItem>

        <TableRowItem>
            <div className='link-url-wrap'>
                <a href={link.url} target="_blank" className="link">
                    {link.url}
                </a>
            </div>
        </TableRowItem>

        <TableRowItem>
            {
                isRechecking ?
                <Loader inline={true} size="small" /> :
                <div className='link-tag-wrap'>
                    <LinkStatusTag status={link.ignored ? -2 : link.status_code} />
                </div>
            }
        </TableRowItem>

        <TableRowItem>

            <Tooltip tooltip="Edit in Editor">
                <a 
                    className='edit-button icon-button'
                    href={postEditUrl}
                    target='_blank'
                    style={{color: 'inherit', fontSize: "0.8rem"}}
                >
                    <PencilFill />
                </a>
            </Tooltip>

            <Tooltip tooltip="Recheck">
                <button 
                    className="icon-button"
                    onClick={handleRecheck}
                >
                    <ArrowClockwise />
                </button>
            </Tooltip>

            <Tooltip tooltip="Ignore this link">
                <button 
                    className={"icon-button" + (link.ignored ? " active" : "")}
                    onClick={handleIgnore}
                >
                    <EyeSlashFill />
                </button>
            </Tooltip>

        </TableRowItem>

    </TableRow>

}
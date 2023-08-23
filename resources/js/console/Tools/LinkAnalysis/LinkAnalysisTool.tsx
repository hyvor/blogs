import React, { Fragment, ReactNode, useEffect, useState } from 'react';
import Tabs from '../../ReusableComponents/Tabs';
import { CardChecklist, CheckCircleFill, ExclamationCircleFill, EyeSlashFill, Gear, Link, Link45deg, XCircleFill } from 'react-bootstrap-icons';
import { Table, TableHead, TableHeadItem, TableRow, TableRowItem } from '../../ReusableComponents/Table';
import api from '../../lib/api';
import { LinkAnalysisLink } from '../../types';
import getSubdomain from '../../logic-helpers/subdomain';
import Loader from '../../ReusableComponents/Loader';
import NoResults from '../../ReusableComponents/NoResults';
import { LinkStatusTag } from '../../Posts/Post/New/Links/LinksComponent';
import Button from '../../ReusableComponents/Button';
import { useLanguagesValues } from '../../Settings/Languages/helpers';
import TableLoadMore from '../../ReusableComponents/TableLoadMore';
import JobStatusBadge from "../../ReusableComponents/JobStatusBadge";

interface Stats {
    counts: {
        ok: number,
        broken: number,
        redirect: number,
        ignored: number
    }
}

export default function LinkAnalysisTool() {

    type TabType = 'links' | 'overview' | 'settings';
    const [tab, setTab] = React.useState<TabType>('overview')

    const [stats, setStats] = useState<null | Stats>(null);

    useEffect(() => {

        api.get<Stats>(getSubdomain(), '/link-analysis/stats')
            .then(res => setStats(res))

    }, []);

    return <div className="tools-link-analysis">

        <Tabs 
            tabs={[
                {label: 'Overview', value: 'overview', icon: <CardChecklist />},
                {label: 'Links', value: 'links', icon: <Link45deg />},
                {label: 'Settings', value: 'settings', icon: <Gear />}
            ]}
            active={tab}
            setActive={tab => setTab(tab as TabType)}
        />

        <div className='tab-content'>
            { tab === 'overview' && <OverviewTab stats={stats} setTab={setTab} /> }
            { tab === 'links' && <LinksTab stats={stats} /> }
        </div>

    </div>

}

function OverviewTab({stats, setTab} : {stats: null | Stats, setTab: Function}) {

    const [analyses, setAnalyses] = useState<LinkAnalysisLink[]>([]);

    function Stat({name, value, icon, id} : {name: ReactNode, icon: ReactNode, value: number, id:string}) {

        return <div className={'stat ' + id}>
            <div className='stat-count'>
                { 
                    value
                }
            </div>
            <div className='name-icon'>
                <span className="icon">{icon}</span>
                {/* <span className="name">{name}</span> */}
            </div>
        </div>

    }

    return <div className="analyses-tab">

        <div className='stats'>

            <div className="stats-title">
                Stats
            </div>


            <div className="stats-note">
                Stats are based on the currently analyzed posts. Some posts may not be analyzed yet, until a full-blog analysis is done.
            </div>


            {

                stats ?

                <div className="stats-inner">

                    <div className="stats-row">

                        <Stat
                            id="ok" 
                            name="OK" 
                            value={stats.counts.ok}
                            icon={<LinkStatusTag status={200} showTooltip={false} />} 
                        />
                        <Stat 
                            id="broken"
                            name="Broken" 
                            value={stats.counts.broken} 
                            icon={<LinkStatusTag status={404} showTooltip={false}  />}
                        />
                        <Stat 
                            id="redirect"
                            name="Redirect" 
                            value={stats.counts.redirect} 
                            icon={<LinkStatusTag status={301} showTooltip={false}  />}
                        />
                        <Stat 
                            id="ignored"
                            name="Ignored" 
                            value={stats.counts.ignored} 
                            icon={<LinkStatusTag status={-2} showTooltip={false}  />}
                        />

                    </div>

                </div> :

                <Loader padding={25} />

            }

            <div className="show-links">
                <Button onClick={() => setTab('links')}>
                    Show Links
                </Button>
            </div>

        </div>

        <div className='stats analyses'>

            <div className="stats-title">
                Analyses
            </div>


            <div className="stats-note">
                Analyses checks all links in your blog every 2 weeks.
            </div>

            <div className="analyses-results">

                <Table columns='1fr 1fr 1fr 1fr 1fr 1fr 1fr'>

                    <TableHead>
                        <TableHeadItem>Date</TableHeadItem>
                        <TableHeadItem>Status</TableHeadItem>
                        <TableHeadItem>No. Posts</TableHeadItem>
                        <TableHeadItem>
                            <LinkStatusTag status={200} showTooltip={false} />
                        </TableHeadItem>
                        <TableHeadItem>
                            <LinkStatusTag status={404} showTooltip={false} />
                        </TableHeadItem>
                        <TableHeadItem>
                            <LinkStatusTag status={301} showTooltip={false} />
                        </TableHeadItem>
                        <TableHeadItem>
                            <LinkStatusTag status={-2} showTooltip={false} />
                        </TableHeadItem>
                    </TableHead>

                    <Fragment>

                        {
                            [{id: 10}].map(analysis => {
                                return <TableRow key={analysis.id}>

                                    <TableRowItem>
                                        Today
                                    </TableRowItem>

                                    <TableRowItem>
                                        <JobStatusBadge status="completed" />
                                    </TableRowItem>

                                    <TableRowItem>
                                        <div>100 posts</div>
                                        <div>200 variants</div>
                                    </TableRowItem>

                                    <TableRowItem>
                                        20
                                    </TableRowItem> 

                                    <TableRowItem>
                                        15
                                    </TableRowItem> 

                                    <TableRowItem>
                                        56
                                    </TableRowItem> 

                                    <TableRowItem>
                                        38
                                    </TableRowItem> 


                                </TableRow>
                            })
                        }

                    </Fragment>

                </Table>

            </div>

        </div>

    </div>

}

function LinksTab({stats} : {stats: null | Stats}) {
    
    type FilterType = null | 'ok' | 'broken' | 'redirect' | 'ignored';
    const [type, setType] = useState<FilterType>(null);

    const subdomain = getSubdomain();
    const [isLoading, setIsLoading] = useState(true);
    const [links, setLinks] = useState<LinkAnalysisLink[]>([]);

    const { getLanguageById, languages } = useLanguagesValues(); 

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

                <Table columns='1fr 2fr 100px'>

                    <TableHead>
                        <TableHeadItem>Post</TableHeadItem>
                        <TableHeadItem>Link</TableHeadItem>
                        <TableHeadItem>Status</TableHeadItem>
                    </TableHead>

                    <Fragment>
                        {
                            links.map(link => {

                                const language = getLanguageById(link.post_variant_language_id);
                                const postEditUrl = `/console/${subdomain}/posts/${link.post_id}`;

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
                                        <div className='edit-post-wrap'>
                                            <a 
                                                className='edit-button'
                                                href={postEditUrl}
                                                target='_blank'
                                            >
                                                Edit
                                            </a>
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
                                        <div className='link-tag-wrap'>
                                            <LinkStatusTag status={link.ignored ? -2 : link.status_code} />
                                        </div>
                                    </TableRowItem>

                                </TableRow>

                            })
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
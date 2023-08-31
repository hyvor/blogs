import React, { Fragment, ReactNode, useEffect, useState } from 'react';
import Tabs from '../../ReusableComponents/Tabs';
import { CardChecklist, Gear, Link45deg } from 'react-bootstrap-icons';
import { Table, TableHead, TableHeadItem, TableRow, TableRowItem } from '../../ReusableComponents/Table';
import api from '../../lib/api';
import { LinkAnalysisCheck, LinkAnalysisLink } from '../../types';
import getSubdomain from '../../logic-helpers/subdomain';
import Loader from '../../ReusableComponents/Loader';
import NoResults from '../../ReusableComponents/NoResults';
import { LinkStatusTag } from '../../Posts/Post/New/Links/LinksComponent';
import Button from '../../ReusableComponents/Button';
import { useLanguagesValues } from '../../Settings/Languages/helpers';
import TableLoadMore from '../../ReusableComponents/TableLoadMore';
import JobStatusBadge from "../../ReusableComponents/JobStatusBadge";
import DualSetting from "../../ReusableComponents/DualSetting";
import Switch from "../../ReusableComponents/Switch";
import dayjs from "dayjs";
import { useBlogActions, useBlogValues } from "../../logic-helpers/blog";
import SettingsSave from "../../ReusableComponents/SettingsSave";
import Radio from "../../ReusableComponents/Radio";
import LinksTab from './LinksTab';
import UpgradeRequired from "../../ReusableComponents/UpgradeRequired";

export interface Stats {
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

    return <UpgradeRequired
        minPlan="growth"
        trialAllowed={true}
        text={
            <div>
                Link Analysis is only available on the <b>Growth plan</b> and above. Upgrade now to automatically analyze all links in your blog and receive email reports.
            </div>
        }
    >
    
        <div className="tools-link-analysis">

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
                { tab === 'settings' && <SettingsTab /> }
            </div>

        </div>

    </UpgradeRequired>

}

function OverviewTab({stats, setTab} : {stats: null | Stats, setTab: Function}) {

    const [analyses, setAnalyses] = useState<LinkAnalysisCheck[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    const [isStartingNew, setIsStartingNew] = useState(false);

    useEffect(() => {

        api.get<LinkAnalysisCheck[]>(getSubdomain(), '/link-analysis/checks')
            .then(res => {
                setIsLoading(false);
                setAnalyses(res)
            })

    }, []);

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

    function handleStartNewAnalysis() {
        setIsStartingNew(true);

        api.post<LinkAnalysisCheck>(getSubdomain(), '/link-analysis/check')
            .then(res => {
                setAnalyses([res, ...analyses]);
            })
            .finally(() => [
                setIsStartingNew(false)
            ])
    }

    return <div className="analyses-tab">

        <div className='stats'>

            <div className="stats-top">

                <div className="stats-top-left">
                    <div className="stats-title">
                        Stats
                    </div>


                    <div className="stats-note">
                        Stats are based on the currently analyzed posts. Some posts may not be analyzed until a full-blog analysis is done.
                    </div>
                </div>

                <div className="stats-top-right">
                    <Button onClick={() => setTab('links')} size="small">
                        See Links
                    </Button>
                </div>


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

        </div>

        <div className='stats analyses'>

            <div className="stats-top">

                <div className="stats-top-left">

                    <div className="stats-title">
                        Analyses
                    </div>

                    <div className="stats-note">
                        A full-blog analysis is done every 2 weeks automatically. You can also start one manually.
                    </div>

                </div>

                <div className="stats-top-right">

                    <Button 
                        size="small" 
                        onClick={handleStartNewAnalysis}
                        loading={isStartingNew}
                    >
                        Start New Analysis
                    </Button>

                </div>

            </div>

            <div className="analyses-results">

                {

                    isLoading ?
                    <Loader padding={40} /> :

                    analyses.length === 0 ?

                    <NoResults text="No analyses" imageWidth={150} /> :
                    <Table columns='2fr 2fr 3fr 2fr 2fr 2fr 2fr'>

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
                                analyses.map(analysis => {
                                    return <TableRow key={analysis.id}>

                                        <TableRowItem>
                                            <time
                                                dateTime={dayjs.unix(analysis.created_at).toISOString()}
                                            >
                                                { dayjs.unix(analysis.created_at).format('YYYY-MM-DD') }
                                            </time>
                                        </TableRowItem>

                                        <TableRowItem>
                                            <JobStatusBadge status={analysis.status} />
                                        </TableRowItem>

                                        <TableRowItem>
                                            <div>{analysis.posts_count} posts ({analysis.post_variants_count} variants)</div>
                                            <div>{analysis.pages_count} pages ({analysis.page_variants_count} variants)</div>
                                        </TableRowItem>

                                        <TableRowItem>
                                            { analysis.links_ok_count }
                                        </TableRowItem> 

                                        <TableRowItem>
                                            { analysis.links_broken_count }
                                        </TableRowItem> 

                                        <TableRowItem>
                                            { analysis.links_redirect_count }
                                        </TableRowItem> 

                                        <TableRowItem>
                                            { analysis.links_ignored_count }
                                        </TableRowItem> 

                                    </TableRow>
                                })
                            }

                        </Fragment>

                    </Table>

                }

            </div>

        </div>

    </div>

}

function SettingsTab() {

    const { blog } = useBlogValues(); 
    const { updateBlogValue } = useBlogActions();

    function handleColorModeChange(value: string) {
        updateBlogValue("link_analysis_email_report", value);
    }

    return <div>

        <DualSetting 
            title="Automated Link Analysis"
            description="Run a full-blog link analysis every 2 weeks automatically."
            right={
                <div>
                    <Switch 
                        checked={blog.link_analysis_enabled}
                        onChange={checked => updateBlogValue('link_analysis_enabled', checked)}
                    />
                </div>
            }
        />

        <DualSetting 
            title="Send email reports"
            description="Send an email report after a full-blog analysis."
            right={
                <div>
                    <Radio
                        name="email-report"
                        placeholder="Never"
                        value="never"
                        onChange={handleColorModeChange}
                        checkFor={blog.link_analysis_email_report}
                    />
                    <Radio
                        name="email-report"
                        placeholder="When Broken Links Found"
                        value="broken"
                        onChange={handleColorModeChange}
                        checkFor={blog.link_analysis_email_report}
                    />
                    <Radio
                        name="email-report"
                        placeholder="Always"
                        value="always"
                        onChange={handleColorModeChange}
                        checkFor={blog.link_analysis_email_report}
                    />
                </div>
            }
        />

        <SettingsSave 
            keys={[
                'link_analysis_enabled',
                'link_analysis_email_report'
            ]}
        />

    </div>

}
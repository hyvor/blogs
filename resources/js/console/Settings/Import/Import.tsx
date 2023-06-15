import React, {useState} from "react";
import DualSetting from "../../ReusableComponents/DualSetting";
import Radio from "../../ReusableComponents/Radio";
import Input from "../../ReusableComponents/Input";
import Switch from "../../ReusableComponents/Switch";
import ActionButton from "../../ReusableComponents/ActionButton";
import {importLogic, SitemapTestInput, SitemapTestResponse} from "../../logic/importLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {useActions} from "kea";
import {toast} from "react-toastify";
import dayjs from "dayjs";
import {
    Popup,
    PopupBodyDefault,
    PopupFooterSingleButton,
    PopupHeaderDefault,
    PopupNotice
} from "../../ReusableComponents/Popup";

import './Import.scss';
import ImportHistory from "./ImportHistory";

export default function Import() {

    const [sitemapUrl, setSitemapUrl] = useState('');
    const [testUrl, setTestUrl] = useState('');

    const [titleSelector, setTitleSelector] = useState('');
    const [descriptionSelector, setDescriptionSelector] = useState('');
    const [contentSelector, setContentSelector] = useState('');
    const [contentExcludeSelector, setContentExcludeSelector] = useState('');
    const [publishedDateSelector, setPublishedDateSelector] = useState('');

    const [slugExclude, setSlugExclude] = useState('');

    const [importMedia, setImportMedia] = useState(true);

    const [isTestLoading, setIsTestLoading] = useState(false);
    const [testData, setTestData] = useState<null | SitemapTestResponse>(null);

    const logic = importLogic({subdomain: getSubdomain()});
    const { sitemapTest, sitemapImport } = useActions(logic);



    return <div className="settings-import">

        <div className="title">
            Import
        </div>

        <DualSetting
            title="Import History"
            right={
                <ImportHistory />
            }
        />

        <DualSetting
            title="Import From"
            description={
                <div>
                    Select a method to import data from
                </div>
            }
            right={
                <Radio
                    placeholder="Sitemap (Live Website)"
                    name="sitemap"
                    value="sitemap"
                    onChange={() => {}}
                    checkFor="sitemap"
                />
            }
        />

        <DualSetting
            title="Sitemap URL"
            description={
                <div>
                    XML or TXT sitemap that contains links to all your posts.
                </div>
            }
            right={
                <Input
                    value={sitemapUrl}
                    onChange={val => setSitemapUrl(val)}
                />
            }
        />

        <DualSetting
            title="CSS Selectors"
            description="Add CSS selectors to find data in your HTML pages"
            right={
                <div>
                    <DualSetting
                        title="Post Title"
                        right={
                            <Input
                                value={titleSelector}
                                onChange={val => setTitleSelector(val)}
                            />
                        }
                    />
                    <DualSetting
                        title="Post Description"
                        right={
                            <Input
                                value={descriptionSelector}
                                onChange={val => setDescriptionSelector(val)}
                            />
                        }
                    />
                    <DualSetting
                        title="Post Content"
                        description="Required"
                        right={
                            <Input
                                value={contentSelector}
                                onChange={val => setContentSelector(val)}
                            />
                        }
                    />
                    <DualSetting
                        title="Post Content Exclude"
                        description="To exclude elements from post content"
                        right={
                            <Input
                                value={contentExcludeSelector}
                                onChange={val => setContentExcludeSelector(val)}
                            />
                        }
                    />
                    <DualSetting
                        title="Post Published Date"
                        right={
                            <Input
                                value={publishedDateSelector}
                                onChange={val => setPublishedDateSelector(val)}
                            />
                        }
                    />
                </div>
            }
        />

        <DualSetting
            title="Import Images"
            description={
                <div>
                    Copy images into blog media (recommended)
                </div>
            }
            right={
                <Switch
                    checked={importMedia}
                    onChange={checked => setImportMedia(checked)}
                />
            }
        />

        <DualSetting
            title="Slug Exclude"
            description={
                <div>
                    Exclude a part of the URL from the slug (ex: /blog/)
                </div>
            }
            right={
                <Input
                    value={slugExclude}
                    onChange={val => setSlugExclude(val)}
                />
            }
        />

        <DualSetting
            title="Test"
            description="Test a single page before importing the sitemap (recommended)"
            right={
                <div>
                    <Input
                        value={testUrl}
                        onChange={val => setTestUrl(val)}
                        placeholder="URL to test"
                    />
                    <div>
                        <ActionButton
                            status={isTestLoading ? "loading" : "stale"}
                            staleName="Test"
                            loadingName="Testing"
                            staleOnClick={() => {

                                if (!testUrl) {
                                    return toast.error("Please enter a URL to test");
                                }

                                setIsTestLoading(true);
                                setTestData(null);

                                sitemapTest({
                                    input: {
                                        url: testUrl,
                                        css: {
                                            title: titleSelector,
                                            description: descriptionSelector,
                                            content: contentSelector,
                                            content_exclude: contentExcludeSelector,
                                            published_date: publishedDateSelector
                                        },
                                        slug_exclude: slugExclude
                                    },
                                    onLoad: (data) => {
                                        setTestData(data);
                                        setIsTestLoading(false);
                                    },
                                    onError: () => {
                                        setIsTestLoading(false);
                                    }
                                })
                            }}
                        />
                    </div>

                    {
                        testData && <Popup

                            className="test-results-popup"

                            header={
                                <PopupHeaderDefault title="Test Results" />
                            }
                            footer={<PopupFooterSingleButton
                                name="Close"
                                onClick={() => setTestData(null)}
                            />}

                            body={
                                <PopupBodyDefault>

                                    <DualSetting
                                        title="URL"
                                        right={<a href={testData.url} className="link" target="_blank">{testData.url}</a>}
                                    />

                                    <DualSetting
                                        title="Slug"
                                        right={testData.data.slug}
                                    />

                                    <DualSetting
                                        title="Title"
                                        right={testData.data.title}
                                    />

                                    <DualSetting
                                        title="Description"
                                        right={testData.data.description}
                                    />

                                    <DualSetting
                                        title="Content"
                                        right={<div
                                            dangerouslySetInnerHTML={{__html: testData.data.content_html}}
                                            className="test-content"
                                        />}
                                    />

                                    <DualSetting
                                        title="Published At"
                                        right={dayjs.unix(testData.data.published_at).format('MMMM D, YYYY')}
                                    />

                                    <DualSetting
                                        title="Featured Image"
                                        right={testData.data.featured_image_url && <img src={testData.data.featured_image_url} />}
                                    />

                                </PopupBodyDefault>
                            }
                        />
                    }

                </div>
            }
        />

        <button onClick={() => {
            sitemapImport({
                input: {
                    sitemap_url: sitemapUrl,
                    css: {
                        title: titleSelector,
                        description: descriptionSelector,
                        content: contentSelector,
                        content_exclude: contentExcludeSelector,
                        published_date: publishedDateSelector
                    },
                    slug_exclude: slugExclude
                }
            })
        }}>
            Import
        </button>

    </div>

}
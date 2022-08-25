import React, {Fragment} from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import Input from '../ReusableComponents/Input';
import Radio from '../ReusableComponents/Radio';
import {useBlogActions, useBlogValues} from "../logic-helpers/blog";
import SettingsSave from "../ReusableComponents/SettingsSave";
import Callout, {CalloutColors} from "../ReusableComponents/Callout";
import {ExclamationCircle} from "react-bootstrap-icons";
import Switch from "../ReusableComponents/Switch";
import {BlogHostingAt} from "../enums";

export default function Hosting() {

    const { blog, blogOriginal } = useBlogValues()
    const { updateBlogValue } = useBlogActions()

    function handleHostedAtChange(value: string) {
        updateBlogValue('hosting_at', value)
    }

    const hostedAt = blog.hosting_at

    return <div className="settings-delete">

        <div className="title">
            Hosting
        </div>

        <DualSetting
            title="Subdomain"
            description="The subdomain part of your hyvorblogs.io domain. This is used to uniquely identify your blog within Hyvor Blogs."
            right={
                <Input
                    type="text"
                    name="subdomain"
                    value={blog.subdomain}
                    onChange={value => updateBlogValue('subdomain', value)}
                />
            }
        />

        <DualSetting 
            title="Hosting on/at"
            description="Where do you like to host your blog?"
            right={
                <div>
                    <div>
                        <Radio 
                            name="hosted-at"
                            placeholder="Subdomain" 
                            value="subdomain" 
                            onChange={handleHostedAtChange}
                            checkFor={hostedAt}
                        />
                        <Radio 
                            name="hosted-at"
                            placeholder="Custom Domain"
                            value="domain" 
                            onChange={handleHostedAtChange}
                            checkFor={hostedAt}
                        />
                        <Radio
                            name="hosted-at"
                            placeholder="Self-hosting" 
                            value="self"
                            onChange={handleHostedAtChange}
                            checkFor={hostedAt}
                        />
                    </div>
                    <p className="global-description">
                        Your blog will be hosted at <b>test.hyvorblogs.io</b>.
                    </p>
                </div>
            }
        />

        {
            hostedAt === 'domain' ?

            <DualSetting 
                title="Custom Domain"
                description="Custom domain "
                right={
                    <Input
                        type="text"
                        name="custom-domain"
                        value={blog.hosting_domain}
                        onChange={value => updateBlogValue('hosting_domain', value)}
                    />
                }
            /> : null

        }

        {

            hostedAt === 'self' ?

            <DualSetting 
                title="Self-hosting URL"
                description="Set the absolute URL where you are self-hosting your blog."
                right={
                    <Input 
                        type="text"
                        name="self-hosting-url"
                        value={blog.hosting_url}
                        onChange={value => updateBlogValue('hosting_url', value)}
                    />
                }
            /> : null

        }

        {
            blogOriginal.subdomain !== blog.subdomain ||
            blogOriginal.hosting_at !== blog.hosting_at ||
            blogOriginal.hosting_domain !== blog.hosting_domain ||
            blogOriginal.hosting_url !== blog.hosting_url ?
                <Callout
                    icon={<ExclamationCircle />}
                    color={CalloutColors.ORANGE}
                    title="Be careful when changing the URL!"
                    text={
                        <div>Changing the hosting URL/Domain can break old URLs, create duplicate pages, and affect SEO. Consult our <a className="link" href="/docs/hosting" target="_blank">documentation</a> for tips on correctly setting up redirects to minimize the risks.</div>
                    }
                /> : null
        }

        {

            blog.hosting_at === BlogHostingAt.SUBDOMAIN &&

            <Fragment>

                <DualSetting
                    title="Embeddable"
                    description={
                        <div>Turn this option on only if you are using <a href="/docs/embedding" className="link" target="_blank">embedding</a>.</div>
                    }
                    right={
                        <div>
                            <Switch
                                checked={blog.embeddable}
                                onChange={checked => updateBlogValue('embeddable', checked)}
                            />
                        </div>
                    }
                />

                {
                    blog.embeddable &&
                    <DualSetting
                        title="Embedding URL"
                        description="Set the absolute URL where you are embedding your blog."
                        right={
                            <div>
                                <Input
                                    type="text"
                                    name="self-hosting-url"
                                    value={blog.embedding_url}
                                    onChange={value => updateBlogValue('embedding_url', value)}
                                />
                            </div>
                        }
                    />
                }

            </Fragment>

        }

        <SettingsSave
            keys={
                [
                    'hosting_url',
                    'hosting_domain',
                    'hosting_at',
                    'subdomain',
                    'embeddable',
                    'embedding_url'
                ]
            }
        />

    </div>

}
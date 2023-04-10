import { useValues } from "kea";
import React, { useState } from "react";
import themeLogic from "../logic/themeLogic";
import Loader from "../ReusableComponents/Loader";
import getSubdomain from "../logic-helpers/subdomain";
import Download from "./Download";
import Upload from "./Upload";
import Changer from "./Changer";
import Folder from "./Folder";
import FilePreview from "./FilePreview/FilePreview";
import { getUserBlogBlog, useBlogValues } from "../logic-helpers/blog";
import Callout from "../ReusableComponents/Callout";
import { Info, InfoCircle } from "react-bootstrap-icons";

export default function Theme() {

    const subdomain = getSubdomain();
    const { loadFilesAjax, files } = useValues(themeLogic({ subdomain }));
    const [displayNavigation, setDisplayNavigation] = useState(true);
    const blog = getUserBlogBlog();

    const themePrefix = `/console/${subdomain}/theme`;

    return <div className="posts-view theme-view">
        <button className="button small navigation-button" onClick={() => setDisplayNavigation(!displayNavigation)}>Files</button>
        <div className="box box-left">
            {
                displayNavigation && <div><div className="middle-heading">
                    <div className="theme-selector">
                        Theme&nbsp;&nbsp;<Changer />
                    </div>

                </div>
                    <div className="theme-left-wrap">

                        <div className="theme-folders">
                            {
                                loadFilesAjax.status === 'loading' ?
                                    <Loader padding={40} /> :

                                    <div>

                                        {
                                            blog.billing_type === 'shopify' &&
                                            <div className="shopify-note">
                                                <Callout
                                                    color="blue"
                                                    icon={<InfoCircle />}
                                                    title="Note"
                                                    text={
                                                        <div>
                                                            This only updates the theme within Hyvor Blogs, not your Shopify Theme. <a href="/docs/shopify#theme" className="link" target="_blank">Learn More</a>
                                                        </div>
                                                    }
                                                />
                                            </div>
                                        }

                                        <Folder name="templates" />
                                        <Folder name="styles" />
                                        <Folder name="assets" />
                                        <Folder name="lang" />
                                        <Folder name={null} />


                                    </div>
                            }
                        </div>
                        <div className="theme-bottom">
                            <Upload />
                            <Download />
                        </div>
                    </div>
                </div>
            }





        </div>
        <div className="box box-right theme-right">
            <FilePreview />
        </div>
    </div>

}
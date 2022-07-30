import React from "react";
import {ThemeFile} from "../../types";
import getSubdomain from "../../logic-helpers/subdomain";
import {useValues} from "kea";
import userBlogsLogic from "../../logic/userBlogsLogic";
import {UserBlog} from "../../objects/userblog";

export default function AssetImagePreview({ file } : { file: ThemeFile }) {

    const subdomain = getSubdomain();
    const { findBlogBySubdomain } = useValues(userBlogsLogic)
    const blog = findBlogBySubdomain(subdomain) as UserBlog

    const url = blog.blog.base_url + '/assets/' + file.name;

    return <div className="asset-image-preview">
        <img src={url} alt={file.name} />
    </div>

}
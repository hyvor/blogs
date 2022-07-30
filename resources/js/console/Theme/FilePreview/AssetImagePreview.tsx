import React from "react";
import {ThemeFile} from "../../types";
import getSubdomain from "../../logic-helpers/subdomain";
import userBlogsLogic from "../../logic/userBlogsLogic";
import {UserBlog} from "../../objects/userblog";

export function getAssetUrl(name: string) {
    const subdomain = getSubdomain();
    const { findBlogBySubdomain } = userBlogsLogic.values
    const blog = findBlogBySubdomain(subdomain) as UserBlog
    return blog.blog.base_url + '/assets/' + name;
}

export default function AssetImagePreview({ file } : { file: ThemeFile }) {

    const url = getAssetUrl(file.name);
    return <div className="asset-image-preview">
        <img src={url} alt={file.name} />
    </div>

}
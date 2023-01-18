import React from "react";
import {SlashCircle} from "react-bootstrap-icons";

export default function BlogBlocked() {

    return <div className="box blog-blocked">

        <SlashCircle size={60} />

        <h2>Your blog is blocked</h2>

        <p>
            We are sorry to inform you that your blog has been blocked due to a violation of our <a href="https://blogs.hyvor.com/docs/terms" className="link">Terms of Service</a>. Your blog will no longer be accessible on the hyvorblogs.io subdomain or a custom domain. API access to this blog is also blocked. If you think this is a mistake, please contact us at <a href="mailto:blogs.support@hyvor.com" className="link">blogs.support@hyvor.com</a>.
        </p>

    </div>;

}
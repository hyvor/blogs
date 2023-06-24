import React, {ReactNode} from "react";
import {A} from "kea-router";
import getSubdomain from "../../logic-helpers/subdomain";
import {CheckCircle, InfoCircle} from "react-bootstrap-icons";
import Callout, {CalloutColors} from "../../ReusableComponents/Callout";

export default function Shopify() {

    const subdomain = getSubdomain();

    return <div className="settings-shopify">

        <div className="title">
            Shopify Guide
        </div>

        <div className="shopify-instructions">

            <Callout
                color={CalloutColors.BLUE}
                icon={<InfoCircle />}
                title="Need help?"
                text={
                    <div>
                        Visit our <a href="/docs/shopify" target="_blank" className="link">Shopify Integration documentation</a> or contact us via live chat.
                    </div>
                }
            />

            <Instruction>This blog is integrated with Shopify</Instruction>
            <Instruction>We have set up your blog within your shop. Visit <b>yourshop.com/a/blog</b> to view it. The blog path is customizable in the App Settings in your Shopify Dashboard.</Instruction>
            <Instruction><b>Recommended!</b> <a href="https://help.shopify.com/en/manual/online-store/menus-and-links/editing-menus#add-a-menu-item" className="link" target="_blank">Add a navigation menu item</a> linking to <b>/a/blog</b> to let users navigate easily to your blog</Instruction>
            <Instruction>You can customize/change the blog theme in the <A href={`/console/${subdomain}/theme`} className={"link"}>Theme</A> section</Instruction>
            <Instruction>You can start a subscription in the <A href={`/console/${subdomain}/billing`} className={"link"}>Billing</A> section. You will be billed through Shopify</Instruction>
            <Instruction>Visit our <a href="/docs" target="_blank" className="link">documentation</a> to learn more about Hyvor Blogs</Instruction>

        </div>

    </div>

}

function Instruction({ children } : { children: ReactNode }) {

    return <div className="instruction">
        <span className="inst-icon"><CheckCircle /></span>
        <div>{ children }</div>
    </div>

}
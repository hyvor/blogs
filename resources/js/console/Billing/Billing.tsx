import React from 'react'
import {getUserBlogBlog} from "../logic-helpers/blog";
import Paddle from "./Paddle/Paddle";
import BillingColumn from "./Components/BillingColumn";
import Shopify from "./Shopify/Shopify";

export default function Billing() {

    const { billing_type } = getUserBlogBlog()

    return <div className="billing-view">

        {
            billing_type === 'paddle' &&
            <Paddle />
        }
        {
            billing_type === 'shopify' &&
            <Shopify />
        }

        <BillingColumn>

            {/*<div className="box billing-section">
                <div className="section-title">
                    Usage
                </div>
                <div className="section-content">
                    <Usage subdomain={subdomain} />
                </div>
            </div>
            <div className="box billing-section">
                <div className="section-title">
                    Billing History
                </div>
                <div className="section-content">
                    <BillingHistory />
                </div>
            </div>
            <div className="box billing-section">
                <div className="section-title">
                    Subscription History
                </div>
                <div className="section-content">
                    <SubscriptionHistory />
                </div>
            </div>*/}

        </BillingColumn>

    </div>

}
import React from 'react'
import {getUserBlogBlog} from "../logic-helpers/blog";
import Paddle from "./Paddle/Paddle";
import BillingColumn from "./Components/BillingColumn";
import Shopify from "./Shopify/Shopify";
import BillingBox from "./Components/BillingBox";
import {Usage} from "./Usage";
import SubscriptionHistory from "./SubscriptionHistory";
import PaddlePaymentHistory from "./Paddle/PaddlePaymentHistory";

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

            <BillingBox>
                <div className="section-title">Usage</div>
                <div className="section-content">
                    <Usage />
                </div>
            </BillingBox>

            {
                billing_type === 'paddle' &&
                <BillingBox>
                    <div className="section-title">Payment History</div>
                    <div className="section-content">
                        <PaddlePaymentHistory />
                    </div>
                </BillingBox>
            }

            <BillingBox>
                <div className="section-title">Subscription History</div>
                <div className="section-content">
                    <SubscriptionHistory />
                </div>
            </BillingBox>

        </BillingColumn>

    </div>

}
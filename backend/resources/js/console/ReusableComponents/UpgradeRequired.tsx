import { Fragment, ReactNode } from "react";
import { SubscriptionPlan } from "../types";
import { getSubscription, isInTrial } from "../lib/blog-helpers";
import getSubdomain from "../logic-helpers/subdomain";
import React from "react";
import { ArrowUpCircle } from "react-bootstrap-icons";
import Button from "./Button";

const allPlanTypes : SubscriptionPlan[] = [
    'starter',
    'growth',
    'premium',
    'team',
    'business',
    'enterprise'
];

export default function UpgradeRequired(
    {minPlan, trialAllowed, children, text, allow} :
    {
        // minimum plan requirement
        minPlan: SubscriptionPlan,
        // whether allowed in the trial period 
        trialAllowed: boolean, 
        // allow to bypass the upgrade required
        allow?: boolean,
        children: ReactNode, 
        text: ReactNode
    }
) {

    const subdomain = getSubdomain();

    const blogSubscription = getSubscription(subdomain);
    const blogInTrial = isInTrial(subdomain);

    const hasMinPlan = blogSubscription && 
        allPlanTypes.indexOf(blogSubscription.plan) >= allPlanTypes.indexOf(minPlan);

    if (hasMinPlan || (trialAllowed && blogInTrial) || allow) {
        return <Fragment>{children}</Fragment>;
    }

    return <div className="global-upgrade-required">

        <div className="upgrade-inner">

            <div className="upgrade-required-title">
                <ArrowUpCircle/> 
                <span>Upgrade Required</span>
            </div>

            <div className="upgrade-required-content">

                { text }

            </div>

            <div className="upgrade-cta">

                <a
                    className="button primary small blue" 
                    href={`/console/` + subdomain + `/billing`}
                    target="_blank"
                >Upgrade Now</a>

            </div>

        </div>


    </div>
    

}
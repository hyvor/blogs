import React from "react";
import {Clock, SlashCircle} from "react-bootstrap-icons";
import NavLink from "../ReusableComponents/NavLink";
import getSubdomain from "../logic-helpers/subdomain";

export default function BlogTrialEnded() {

    const subdomain = getSubdomain();

    return <div className="box blog-blocked">

        <Clock size={60} />

        <h2>Your trial has ended</h2>

        <p>
            Your 7-days trial has ended. You can activate your blog for one-time fee, or upgrade to a subscription plan if you need more resources. See <a className="link" href="/pricing" target="_blank">pricing</a> for more details. If you are a student or low-income individual, contact us via live chat to get your blog activated for free.
        </p>

        <p>
            <button className="button medium">
                <NavLink href={"/console/" + subdomain + "/billing"}>Go to Billing</NavLink>
            </button>
        </p>

    </div>;

}
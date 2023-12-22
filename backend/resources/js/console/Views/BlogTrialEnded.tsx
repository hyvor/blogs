import React from "react";
import {Clock, SlashCircle} from "react-bootstrap-icons";
import NavLink from "../ReusableComponents/NavLink";
import getSubdomain from "../logic-helpers/subdomain";
import { router } from "kea-router";

export default function BlogTrialEnded() {

    const subdomain = getSubdomain();

    function gotoBilling() {
        router.actions.push("/console/" + subdomain + "/billing");
    }

    return <div className="box blog-blocked">

        <Clock size={60} />

        <h2>Your trial has ended</h2>

        <p>
            Your 7-days trial has ended. Upgrade to a paid plan to continue using your blog. See <a className="link" href="/pricing" target="_blank">pricing</a> for more details. If you have any questions, feel free to contact us.
        </p>

        <p>
            <button className="button medium" onClick={gotoBilling}>
                Go to Billing
            </button>
        </p>

    </div>;

}
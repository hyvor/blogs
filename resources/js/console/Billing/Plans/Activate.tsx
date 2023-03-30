import React from "react";
import {getUserBlogBlog} from "../../logic-helpers/blog";
import {Check, CheckCircle, Clock, ExclamationCircle} from "react-bootstrap-icons";
import Callout from "../../ReusableComponents/Callout";
import dayjs from "dayjs";

export default function Activate() {

    const blog = getUserBlogBlog();
    const trialDays = dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'day');
    const hasTrialEnded = trialDays <= 0;

    const type = blog.is_activated ? 'active' : (
        hasTrialEnded ? 'expired' : 'trial'
    );

    let title = 'Starter Plan Activated';
    if (type === 'expired')
        title = 'Trial Ended';
    if (type === 'trial')
        title = 'Trial';

    return <div className="activate-wrap">

        <Callout
            title={title}
            icon={
                type === 'active' ? <CheckCircle /> : (
                    type === 'expired' ? <ExclamationCircle /> : <Clock />
                )
            }
            text={
                <div>
                    {
                        type === 'active' && "This blog is activated."
                    }
                    {
                        type === 'expired' && "Trial has ended. Activate the starter plan or upgrade to a subscription plan to continue using the blog."
                    }
                    {
                        type === 'trial' && "Trial ends in " + trialDays + " days. Activate the starter plan or upgrade to a subscription plan to continue using the blog."
                    }
                    {
                        (type === 'trial' || type === 'expired') &&
                        <div className="activate-button">
                            <button className="button medium">Activate for $5</button>
                        </div>
                    }
                </div>
            }
            color="blue"
        />

    </div>

}
import React, {useState} from "react";
import {getUserBlogBlog} from "../../logic-helpers/blog";
import {Check, CheckCircle, Clock, ExclamationCircle} from "react-bootstrap-icons";
import Callout from "../../ReusableComponents/Callout";
import dayjs from "dayjs";
import {ConsoleWindow} from "../../types";
import getSubdomain from "../../logic-helpers/subdomain";
import paddleLogic from "../../logic/billing/paddleLogic";
import {useActions} from "kea";

export default function Activate() {

    const blog = getUserBlogBlog();
    const trialDays = dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'day');
    const hasTrialEnded = trialDays <= 0;

    const subdomain = getSubdomain();
    const paddleLogicInst = paddleLogic({subdomain});
    const { createActivation } = useActions(paddleLogicInst);

    const type = blog.is_activated ? 'active' : (
        hasTrialEnded ? 'expired' : 'trial'
    );

    let title = 'Starter Plan Activated';
    if (type === 'expired')
        title = 'Trial Ended';
    if (type === 'trial')
        title = 'Trial';

    const [checkoutLoading, setCheckoutLoading] = useState(false);

    function handleActivate() {

        createActivation({
            onLoad: (payLink: string) => {
                setCheckoutLoading(true);
                (window as ConsoleWindow).Paddle.Checkout.open({
                    override: payLink,
                    loadCallback: () => {
                        setCheckoutLoading(false);
                    },
                    successCallback: () => {

                    }
                });
            }
        });

    }

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
                            <button className="button medium" onClick={handleActivate}>Activate for $5</button>
                        </div>
                    }
                </div>
            }
            color="blue"
        />

    </div>

}
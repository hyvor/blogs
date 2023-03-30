import React, {useState} from "react";
import {getUserBlogBlog} from "../../logic-helpers/blog";
import {Check, CheckCircle, Clock, ExclamationCircle} from "react-bootstrap-icons";
import Callout from "../../ReusableComponents/Callout";
import dayjs from "dayjs";
import {ConsoleWindow} from "../../types";
import getSubdomain from "../../logic-helpers/subdomain";
import paddleLogic from "../../logic/billing/paddleLogic";
import {useActions, useValues} from "kea";
import {FullPageLoader} from "../../ReusableComponents/Loader";
import {PopupNotice} from "../../ReusableComponents/Popup";

export default function Activate() {

    const blog = getUserBlogBlog();
    const trialDays = dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'day');
    const hasTrialEnded = trialDays <= 0;

    const subdomain = getSubdomain();
    const paddleLogicInst = paddleLogic({subdomain});
    const { createActivation } = useActions(paddleLogicInst);
    const { createActivationAjax } = useValues(paddleLogicInst)

    const type = blog.is_activated ? 'active' : (
        hasTrialEnded ? 'expired' : 'trial'
    );

    let title = 'Starter Plan Activated';
    if (type === 'expired')
        title = 'Trial Ended';
    if (type === 'trial')
        title = 'Trial';

    const [checkoutSuccess, setCheckoutSuccess] = useState(false);
    const [reloadCountdown, setReloadCountdown] = useState(10);

    function handleActivate() {

        createActivation({
            onLoad: (payLink: string) => {
                (window as ConsoleWindow).Paddle.Checkout.open({
                    override: payLink,
                    loadCallback: () => {

                    },
                    successCallback: () => {
                        setCheckoutSuccess(true);
                        setReloadCountdown(10);
                        setInterval(() => {
                            setReloadCountdown(reloadCountdown => Math.max(0, reloadCountdown - 1));
                        }, 1000);
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

        {
            createActivationAjax.status === 'loading' && <FullPageLoader />
        }

        {
            checkoutSuccess && <PopupNotice
                title="Payment successful"
                text={<div>Your payment was successful. It will take a few moments for changes to appear in the console. Please reload the page {reloadCountdown !== 0 ? `in ${reloadCountdown} seconds` : "now" }.</div>}
                name={reloadCountdown !== 0 ? `Reload in ${reloadCountdown} seconds` : "Reload"}
                buttonClass={reloadCountdown !== 0 ? "disabled inactive" : ""}
                onClick={() => location.reload()}
            />
        }

    </div>

}
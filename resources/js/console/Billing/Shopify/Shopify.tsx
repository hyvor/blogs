import React, {Fragment, useState} from 'react';
import BillingColumn from "../Components/BillingColumn";
import BillingBox from "../Components/BillingBox";
import Plans from "../Plans/Plans";
import {SubscriptionFrequency, SubscriptionPlan} from "../../types";
import api from "../../lib/api";
import getSubdomain from "../../logic-helpers/subdomain";
import {FullPageLoader} from "../../ReusableComponents/Loader";

export default function Shopify() {

    const subdomain = getSubdomain();
    const [ isLoading, setIsLoading ] = useState(false)

    async function handleCreate(plan: SubscriptionPlan, frequency: SubscriptionFrequency) {

        setIsLoading(true)

        type Response = { link: string }
        const data = await api.post<Response>(subdomain, '/billing/shopify/subscription', {
            plan, frequency
        });

        location.href = data.link;
    }

    async function handleCancel() {
        setIsLoading(true)
        await api.delete(subdomain, '/billing/shopify/subscription');
        location.reload()
    }

    return <Fragment>

        <BillingColumn>
            <BillingBox>
                <Plans
                    onSubscriptionCreate={handleCreate}
                    onSubscriptionUpdate={handleCreate}
                    onSubscriptionCancel={handleCancel}
                />
            </BillingBox>
        </BillingColumn>

        { isLoading && <FullPageLoader /> }

    </Fragment>

}
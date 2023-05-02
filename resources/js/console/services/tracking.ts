import { getPriceFromPlan } from "../Billing/Plans/Plan";
import { UserBlog } from "../objects/userblog";
import { SubscriptionFrequency, SubscriptionPlan } from "../types";


class Tracking {

    trackBlogCreate(userBlog: UserBlog) {
        this.bingTrack('blog-created', 0);
        this.splitbeeTrack("Blog Created", {subdomain: userBlog.blog.subdomain})
    }

    trackSubscriptionCreate(plan: SubscriptionPlan, frequency: SubscriptionFrequency) {
        const price = getPriceFromPlan(plan);

        this.bingTrack('subscription-created', price);
        this.splitbeeTrack("Subscription Created", {plan, frequency, price})

    }

    private splitbeeTrack(event: string, data: object) {
        const w = window as any;
        if (w.splitbee) {
            w.splitbee.track(event, data)
        }
    }

    private bingTrack(event: string, price: number) {
        const w = window as any;
        if (w.uetq) {
            w.uetq.push('event', event, {revenue_value: price, currency: 'USD'});
        }
    }

}


const tracking = new Tracking;
export default tracking;
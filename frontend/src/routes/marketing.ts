import { Crisp } from "crisp-sdk-web";
import splitbee from '@splitbee/web';

function initCrisp() {
    Crisp.configure("1cab78fb-4baf-497e-a10f-00a3b12cfcfe");
}

function initSplitbee() {
    splitbee.init()


    window.addEventListener('console:blog:created', (event) => {
        const subdomain = (event as CustomEvent).detail.subdomain;
        splitbee.track("Blog Created", {subdomain})
    });

    window.addEventListener('console:subscription:created', (event) => {
        const {plan, frequency, price} = (event as CustomEvent).detail;
        splitbee.track("Subscription Created", {plan, frequency, price})
    });
}

export function setUpMarketing() {
    initCrisp();
    initSplitbee();
}
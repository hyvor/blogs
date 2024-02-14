import { Crisp } from "crisp-sdk-web";
import splitbee from '@splitbee/web';
import posthog from 'posthog-js'
import { browser } from "$app/environment";
import { afterNavigate, beforeNavigate } from "$app/navigation";

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

function initPosthog() {
    posthog.init('phc_75BsBwYy8qSn2Vsox8ZGyxiyweG1rPd1UemYHT2GI0p', { api_host: 'https://eu.posthog.com' })

    window.addEventListener('console:blog:created', (event) => {
        const subdomain = (event as CustomEvent).detail.subdomain;
        posthog.capture('blog_created', {subdomain});
    });

    window.addEventListener('console:temp_blog:created', (event) => {
        const subdomain = (event as CustomEvent).detail.subdomain;
        posthog.capture('temp_blog_created', {subdomain});
    });

    window.addEventListener('console:subscription:created', (event) => {
        const {plan, frequency, price} = (event as CustomEvent).detail;
        posthog.capture('subscription_created', {plan, frequency, price});
    });

    beforeNavigate(() => posthog.capture('$pageleave'));
    afterNavigate(() => posthog.capture('$pageview'));
}

export function setUpMarketing() {
    if (browser) {
        initCrisp();
        initSplitbee();
        initPosthog();
    }
}
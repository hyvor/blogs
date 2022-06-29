# Analytics

It is cool to know some metrics about how much traffic your blog gets. You can easily integrate analytic services to the blog using [custom code](custom-code).

## How to add {#how}

* First, sign up for an [analytics service](#services)
* Copy the given HTML code
* Paste that code in **Console &rarr; Settings &rarr; Custom Code &rarr; Foot Code**

## Analytics Services {#services}

* [Google Analytics](https://analytics.google.com/analytics/web/)
* [Cloudflare Analytics](https://www.cloudflare.com/analytics)
* [Splitbee](https://splitbee.io/) (We use this ❤️)
* [Matomo](https://matomo.org/)
* [Fathom](https://usefathom.com)
* [Plausible Analytics](https://plausible.io)
* [Simple Analytics](https://simpleanalytics.io/)
* [Posthog](https://posthog.com/)

We are not affiliated with any of these services. There are plenty of other analytics tools not mentioned here.

## Privacy

Some of those platforms track **users** while others only track **visits**. Before tracking users, you will need the user's consent (Using a **cookie banner**). Please consult the documentations of each service to learn more about how they handle personal data.

## Why not native analytics?

There are two reasons we do not provide analytics natively within Hyvor Blogs:

* Hyvor Blogs is designed to use [edge caching](#edge-caching). 99% of the time, requests from users do not even reach our servers. Therefore, we do not have a way to track them.
* If you have other websites, you may already be using an analytics solution. Also, if you have multiple blogs, you can use the same analytics service. Most of the services mentioned above charge for pageviews, not number of websites. So, it is easier for you to have one service for all of your websites.

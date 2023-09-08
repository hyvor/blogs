/// <reference types="vite/client" />
import * as Sentry from "@sentry/react";

if (import.meta.env.PROD) {

    Sentry.init({
        environment: 'production',
        dsn: "https://84ac9790578509bff55b22401ea8356a@o1093814.ingest.sentry.io/4505824665206784",
        integrations: [
            new Sentry.BrowserTracing({
                // Set 'tracePropagationTargets' to control for which URLs distributed tracing should be enabled
                tracePropagationTargets: [/^https:\/\/blogs.hyvor.com/],
            }),
            new Sentry.Replay(),
        ],
        // Performance Monitoring
        tracesSampleRate: 0, // Capture 100% of the transactions, reduce in production!
        // Session Replay
        replaysSessionSampleRate: 0, // This sets the sample rate at 10%. You may want to change it to 100% while in development and then sample at a lower rate in production.
        replaysOnErrorSampleRate: 1.0, // If you're not already sampling the entire session, change the sample rate to 100% when sampling sessions where errors occur.
    });

}
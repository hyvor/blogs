import googleAnalyticsIcon from './icons/google-analytics.png';
import facebookPixelIcon from './icons/facebook-pixel.png';
import cloudflareAnalyticsIcon from './icons/cloudflare-analytics.png';
import fathomIcon from './icons/fathom.svg';
import matomoIcon from './icons/matomo.png';
import plausibleIcon from './icons/plausible-analytics.png';
import simpleAnalyticsIcon from './icons/simple-analytics.svg';
import posthogIcon from './icons/posthog.svg';
import zohoAnalyticsIcon from './icons/zoho-analytics.png';
import hyvortalkIcon from './icons/hyvor-talk.svg';
import disqusIcon from './icons/disqus.png';
import getreplyboxIcon from './icons/getreplybox.png';
import commentoIcon from './icons/commento.png';
import convertkitIcon from './icons/convertkit.svg';
import mailchimpIcon from './icons/mailchimp.svg';
import emailoctopusIcon from './icons/emailoctopus.svg';
import moosendIcon from './icons/moosend.png';
import mailerliteIcon from './icons/mailerlite.png';
import memberstackIcon from './icons/memberstack.svg';
import memberspaceIcon from './icons/memberspace.png';
import googleFormsIcon from './icons/google-forms.png';
import microsoftFormsIcon from './icons/microsoft-forms.png';
import typeformIcon from './icons/typeform.png';
import wufooIcon from './icons/wufoo.png';
import jotformIcon from './icons/jotform.png';
import formsiteIcon from './icons/formsite.png';
import paperformIcon from './icons/paperform.png';
import zohoFormsIcon from './icons/zoho-forms.png';
import googleAdsenseIcon from './icons/google-adsense.svg';
import ezoicImg from './icons/ezoic.png';

import type { ComponentType } from "svelte";

import GoogleAnalytics from './content/google-analytics/GoogleAnalytics.svelte';
import CloudflareAnalytics from './content/cloudflare-anaytics/CloudflareAnalytics.svelte';
import HyvorTalkComments from './content/hyvor-talk-comments/HyvorTalkComments.svelte';
import Mailchimp from './content/mailchimp/Mailchimp.svelte';
import Ezoic from './content/ezoic/Ezoic.svelte';
import GoogleAdsense from './content/google-adsense/GoogleAdsense.svelte';
import GoogleForms from './content/google-forms/GoogleForms.svelte';
import Memberstack from './content/memberstack/Memberstack.svelte';


type CategoryName = 'analytics' | 'comments' | 'newsletters' | 'ads' | 'memberships' | 'forms';


interface Integration {
    slug: string,
    name: string,
    subname?: string,
    icon: string,
    title: string,
    description: string,
    category: CategoryName,
    color: string,
    component?: ComponentType
}


export const integrations : Integration[] = [
    //analytics
    // google analytics

    {
        slug: 'google-analytics',
        name: 'Google Analytics',
        icon: googleAnalyticsIcon,
        title: 'Add Google Analytics to your blog',
        description: 'Google Analytics is a widely-used analytics service for tracking and reporting website traffic and user behavior.',
        category: 'analytics',
        color: '#e7c565',
        component: GoogleAnalytics
    },

    // cloudflare analytics
    {
        slug: 'cloudflare-analytics',
        name: 'Cloudflare Analytics',
        icon: cloudflareAnalyticsIcon,
        title: 'Add Cloudflare Analytics to your blog',
        description: "Cloudflare Analytics provides website performance and security insights, integrated with Cloudflare's CDN services. Learn how to integrate Cloudflare Analytics with your Hyvor Blogs blog.",
        category: 'analytics',
        color: '#ffdab8',
        component: CloudflareAnalytics
    },

    //matomo
    // {
    //     slug: 'matomo',
    //     name: 'Matomo',
    //     icon: matomoIcon,
    //     title: 'Add Matomo Analytics to your blog',
    //     description: 'Matomo Analytics is a privacy-focused, open-source web analytics platform for tracking and analyzing website visitor data. Learn how to integrate Matomo Analytics with your Hyvor Blogs blog.',
    //     category: 'analytics',
    //     color: '#dbe5ff',
        
    // },

    //fathom
    // {
    //     slug: 'fathom',
    //     name: 'Fathom',
    //     icon: fathomIcon,
    //     title: 'Add Fathom Analytics to your blog',
    //     description: 'Fathom is a simple, privacy-focused analytics platform that offers basic website traffic insights without using cookies or tracking personal data. Learn how to integrate Fathom Analytics with your Hyvor Blogs blog.',
    //     category: 'analytics',
    //     color: '#e4e0fa'
    // },

    //plausible analytics
    // {
    //     slug: 'plausible-analytics',
    //     name: 'Plausible Analytics',
    //     icon: plausibleIcon,
    //     title: 'Add Plausible Analytics to your blog',
    //     description: 'Plausible is a lightweight, privacy-friendly alternative to Google Analytics, offering simple yet insightful website analytics with a focus on user privacy. Learn how to integrate Plausible Analytics with your Hyvor Blogs blog.',
    //     category: 'analytics',
    //     color: '#eceeff'
    // },

    //simple analytics
    // {
    //     slug: 'simple-analytics',
    //     name: 'Simple Analytics',
    //     icon: simpleAnalyticsIcon,
    //     title: 'Add Simple Analytics to your blog',
    //     description: 'Simple Analytics is a privacy-friendly web analytics tool that provides basic website traffic metrics while prioritizing user anonymity and data protection. Learn how to integrate Simple Analytics with your Hyvor Blogs blog.',
    //     category: 'analytics',
    //     color: '#ffd6da'
    // },

    //posthog
    // {
    //     slug: 'posthog',
    //     name: 'Posthog',
    //     icon: posthogIcon,
    //     title: 'Add Posthog Analytics to your blog',
    //     description: 'Posthog is an open-source product analytics platform that offers advanced user behavior tracking and insights for web applications. Learn how to integrate Posthog Analytics with your Hyvor Blogs blog.',
    //     category: 'analytics',
    //     color: '#e1e1e1'
    // },

    //zoho analytics
    // {
    //     slug: 'zoho-analytics',
    //     name: 'Zoho Analytics',
    //     icon: zohoAnalyticsIcon,
    //     title: 'Add Zoho Analytics to your blog',
    //     description: 'Zoho Analytics is a cloud-based business intelligence and analytics platform that provides advanced data visualization and reporting capabilities. Learn how to integrate Zoho Analytics with your Hyvor Blogs blog.',
    //     category: 'analytics',
    //     color: '#ffcdc8'
    // },

    //comments
    //hyvor talk
    {
        slug: 'hyvor-talk-comments',
        name: 'Hyvor Talk',
        subname: 'Comments',
        icon: hyvortalkIcon,
        title: 'Add Hyvor Talk Comments to your blog',
        description: 'Hyvor Talks is a privacy-focused, feature-rich commenting system for websites, offering real-time discussions with spam protection and user-friendly interface. Learn how to integrate Hyvor Talk Comments with your Hyvor Blogs blog.',
        category: 'comments',
        color: '#ffe6a4',
        component: HyvorTalkComments
    },

    //disqus
    // {
    //     slug: 'disqus',
    //     name: 'Disqus',
    //     icon: disqusIcon,
    //     title: 'Add Disqus to your blog',
    //     description: 'Disqus is a commenting platform that offers real-time discussions, spam protection, and user engagement features for websites. Learn how to integrate Disqus with your Hyvor Blogs blog. Learn how to integrate Disqus with your Hyvor Blogs blog.',
    //     category: 'comments',
    //     color: '#c9e7ff'
    // },

    //commento
    // {
    //     slug: 'commento',
    //     name: 'Commento',
    //     icon: commentoIcon,
    //     title: 'Add Commento to your blog',
    //     description: 'Commento is a fast, privacy-focused commenting platform that offers real-time discussions and user engagement features for websites. Learn how to integrate Commento with your Hyvor Blogs blog.',
    //     category: 'comments',
    //     color: '#c0c0c0'
    // },

    //getreplybox
    // {
    //     slug: 'getreplybox',
    //     name: 'GetReplyBox',
    //     icon: getreplyboxIcon,
    //     title: 'Add GetReplyBox to your blog',
    //     description: 'GetReplyBox is a privacy-focused, feature-rich commenting system for websites, offering real-time discussions with spam protection and user-friendly interface. Learn how to integrate GetReplyBox with your Hyvor Blogs blog.',
    //     category: 'comments',
    //     color: '#e8def3'
    // },

    //newsletters
    // {
    //     slug: 'hyvor-talk-newsletters',
    //     name: 'Hyvor Talk',
    //     subname: 'Newsletters',
    //     icon: hyvortalkIcon,
    //     title: 'Add Hyvor Talk Newsletters to your blog',
    //     description: 'Hyvor Talk is a privacy-focused newsletter platform for websites and blogs. Learn how to integrate Hyvor Talk Newsletters with your Hyvor Blogs blog.',
    //     category: 'newsletters',
    //     color: '#ffe6a4'
    // },

    //convertkit
    // {
    //     slug: 'convertkit',
    //     name: 'ConvertKit',
    //     icon: convertkitIcon,
    //     title: 'Add ConvertKit to your blog',
    //     description: 'ConvertKit is an email marketing platform that offers advanced automation and segmentation features for growing your newsletter list. Learn how to integrate ConvertKit with your Hyvor Blogs blog.',
    //     category: 'newsletters',
    //     color: '#fee4e5'
    // },

    //mailchimp
    {
        slug: 'mailchimp',
        name: 'Mailchimp',
        icon: mailchimpIcon,
        title: 'Add Mailchimp to your blog',
        description: 'Mailchimp is an all-in-one marketing platform that offers advanced email marketing and automation features for growing your newsletter list. Learn how to integrate Mailchimp with your Hyvor Blogs blog.',
        category: 'newsletters',
        color: '#fae2c4',
        component: Mailchimp
    },

    //emailoctopus
    // {
    //     slug: 'emailoctopus',
    //     name: 'EmailOctopus',
    //     icon: emailoctopusIcon,
    //     title: 'Add EmailOctopus to your blog',
    //     description: 'EmailOctopus is an email marketing platform that offers advanced automation and segmentation features for growing your newsletter list. Learn how to integrate EmailOctopus with your Hyvor Blogs blog.',
    //     category: 'newsletters',
    //     color: '#ddd8f2'
    // },

    //mossend
    // {
    //     slug: 'moosend',
    //     name: 'Moosend',
    //     icon: moosendIcon,
    //     title: 'Add Moosend to your blog',
    //     description: 'Moosend is an email marketing platform that offers advanced automation and segmentation features for growing your newsletter list. Learn how to integrate Moosend with your Hyvor Blogs blog.',
    //     category: 'newsletters',
    //     color: '#e9dadf'
    // },

    //mailerlite
    // {
    //     slug: 'mailerlite',
    //     name: 'MailerLite',
    //     icon: mailerliteIcon,
    //     title: 'Add MailerLite to your blog',
    //     description: 'MailerLite is an email marketing platform that offers advanced automation and segmentation features for growing your newsletter list. Learn how to integrate MailerLite with your Hyvor Blogs blog.',
    //     category: 'newsletters',
    //     color: '#c0ecd7'
    // },

    //memberships
    //hyvor talk
    // {
    //     slug: 'hyvor-talk-memberships',
    //     name: 'Hyvor Talk',
    //     subname: 'Memberships',
    //     icon: hyvortalkIcon,
    //     title: 'Add Hyvor Talk Memberships to your blog',
    //     description: 'Hyvor Talk is a privacy-focused, feature-rich memberships platform. Learn how to integrate Hyvor Talk Memberships with your Hyvor Blogs blog.',
    //     category: 'memberships',
    //     color: '#ffe6a4'
    // },

    //memberstack
    // {
    //     slug: 'memberstack',
    //     name: 'Memberstack',
    //     icon: memberstackIcon,
    //     title: 'Add Memberstack to your blog',
    //     description: 'Memberstack is a membership platform that offers advanced automation and segmentation features for growing your newsletter list. Learn how to integrate Memberstack with your Hyvor Blogs blog.',
    //     category: 'memberships',
    //     color: '#e2f3ff',
    //     component: Memberstack
    // },

    //memberspace
    // {
    //     slug: 'memberspace',
    //     name: 'Memberspace',
    //     icon: memberspaceIcon,
    //     title: 'Add Memberspace to your blog',
    //     description: 'Memberspace is a membership platform that offers advanced automation and segmentation features for growing your newsletter list. Learn how to integrate Memberspace with your Hyvor Blogs blog.',
    //     category: 'memberships',
    //     color: '#ffdfef'
    // },

    //forms
    //google forms
    {
        slug: 'google-forms',
        name: 'Google Forms',
        icon: googleFormsIcon,
        title: 'Add Google Forms to your blog',
        description: 'Google Forms is a widely-used web forms service offered by Google for creating and managing online forms and surveys. Learn how to integrate Google Forms with your Hyvor Blogs blog.',
        category: 'forms',
        color: '#d1b8e7',
        component: GoogleForms
    },

    //microsft forms
    // {
    //     slug: 'microsoft-forms',
    //     name: 'Microsoft Forms',
    //     icon: microsoftFormsIcon,
    //     title: 'Add Microsoft Forms to your blog',
    //     description: 'Microsoft Forms is a widely-used web forms service offered by Microsoft for creating and managing online forms and surveys. Learn how to integrate Microsoft Forms with your Hyvor Blogs blog.',
    //     category: 'forms',
    //     color: '#adeff2'
    // },

    //typeform
    // {
    //     slug: 'typeform',
    //     name: 'Typeform',
    //     icon: typeformIcon,
    //     title: 'Add Typeform to your blog',
    //     description: 'Typeform is an interactive form and survey builder known for its intuitive, conversational design, allowing users to create engaging, customizable forms with ease. Learn how to integrate Typeform with your Hyvor Blogs blog.',
    //     category: 'forms',
    //     color: '#e5e5e5'
    // },

    //wufoo
    // {
    //     slug: 'wufoo',
    //     name: 'Wufoo',
    //     icon: wufooIcon,
    //     title: 'Add Wufoo to your blog',
    //     description: 'Wufoo is a web forms service that offers a user-friendly form builder with advanced customization and integration capabilities. Learn how to integrate Wufoo with your Hyvor Blogs blog.',
    //     category: 'forms',
    //     color: '#ffd1cb'
    // },

    //jotform
    // {
    //     slug: 'jotform',
    //     name: 'Jotform',
    //     icon: jotformIcon,
    //     title: 'Add Jotform to your blog',
    //     description: 'JotForm is a versatile online form builder offering a wide range of templates, customization options, and integrations for creating forms, surveys, and registrations. Learn how to integrate Jotform with your Hyvor Blogs blog.',
    //     category: 'forms',
    //     color: '#ffe2a6'
    // },

    //formsite
    // {
    //     slug: 'formsite',
    //     name: 'Formsite',
    //     icon: formsiteIcon,
    //     title: 'Add Formsite to your blog',
    //     description: 'Formsite is a user-friendly form builder with advanced features such as conditional logic, payment integration, and custom branding for creating professional forms and surveys.',
    //     category: 'forms',
    //     color: '#c4e3ff'
    // },

    //paperform
    // {
    //     slug: 'paperform',
    //     name: 'Paperform',
    //     icon:  paperformIcon,
    //     title: 'Add Paperform to your blog',
    //     description: 'Paperform is an elegant online form builder that combines the simplicity of a form with the richness of a document, allowing for beautiful, customizable forms and surveys. Learn how to integrate Paperform with your Hyvor Blogs blog.',
    //     category: 'forms',
    //     color: '#e2e2e2'
    // },

    //zoho forms
    // {
    //     slug: 'zoho-forms',
    //     name: 'Zoho Forms',
    //     icon: zohoFormsIcon,
    //     title: 'Add Zoho Forms to your blog',
    //     description: 'Zoho Forms is a part of the Zoho suite, offering a comprehensive form builder with workflow automation, analytics, and integration capabilities for creating efficient online forms. Learn how to integrate Zoho Forms with your Hyvor Blogs blog.',
    //     category: 'forms',
    //     color: '#c2f1d2'
    // },
    

    //ads
    //facebook pixel

    // {
    //     slug: 'facebook-pixel',
    //     name: 'Facebook Pixel',
    //     icon: facebookPixelIcon,
    //     title: "Add Facebook Pixel to your blog",
    //     description: 'Facebook Pixel is a tracking tool by Facebook for measuring and optimizing ad performance, helping businesses understand user behavior across their websites and target audiences effectively. Learn how to integrate Facebook Pixel with your Hyvor Blogs blog.',
    //     category: 'ads',
    //     color: "#e0eaff"
    // },

    //google adsense
    {
        slug: 'google-adsense',
        name: 'Google Adsense',
        icon: googleAdsenseIcon,
        title: 'Add Google AdSense to your blog',
        description: 'Google AdSense is a widely-used advertising platform by Google that allows website owners to monetize their content by displaying targeted ads to visitors. Learn how to integrate Google AdSense with your Hyvor Blogs blog.',
        category: 'ads',
        color: '#d9e1ef',
        component: GoogleAdsense
    },

    //ezoic
    // {
    //     slug: 'ezoic',
    //     name: 'Ezoic',
    //     icon: ezoicImg,
    //     title: 'Add Ezoic to your blog',
    //     description: 'Ezoic focuses on intelligent technology to enhance online content, aiming to improve revenue, performance, and traffic for publishers. Learn how to integrate Ezoic with your Hyvor Blogs blog.',
    //     category: 'ads',
    //     color: '#d2f4b4',
    //     component: Ezoic
    // },



    
];

interface Catergory {
    name: CategoryName,
    title: string,
    integrations: Integration[]
}

export const categories : Catergory[] = [

    {
        name: 'analytics',
        title: 'Analytics',
        integrations: integrations.filter(integration => integration.category === 'analytics'),
    },
    {
        name: 'comments',
        title: 'Comments',
        integrations: integrations.filter(integration => integration.category === 'comments')
    },
    {
        name: 'newsletters',
        title: 'Newsletters',
        integrations: integrations.filter(integration => integration.category === 'newsletters')
    },

    {
        name: 'ads',
        title: 'Ads',
        integrations: integrations.filter(integration => integration.category === 'ads')
    },

    // {
    //     name: 'memberships',
    //     title: 'Memberships',
    //     integrations: integrations.filter(integration => integration.category === 'memberships')
    // },

    {
        name: 'forms',
        title: 'Forms',
        integrations: integrations.filter(integration => integration.category === 'forms')
    }


]
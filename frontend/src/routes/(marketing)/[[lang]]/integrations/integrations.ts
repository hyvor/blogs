// import ezoicImg from './icons/ezoic.png';

import type { Component } from 'svelte';

import GoogleAnalytics from './content/google-analytics/GoogleAnalytics.svelte';
import CloudflareAnalytics from './content/cloudflare-anaytics/CloudflareAnalytics.svelte';
import Matomo from './content/matomo/Matomo.svelte';
import HyvorTalkComments from './content/hyvor-talk-comments/HyvorTalkComments.svelte';
import Mailchimp from './content/mailchimp/Mailchimp.svelte';
// import Ezoic from './content/ezoic/Ezoic.svelte';
import GoogleAdsense from './content/google-adsense/GoogleAdsense.svelte';
import GoogleForms from './content/google-forms/GoogleForms.svelte';
import Memberstack from './content/memberstack/Memberstack.svelte';

type CategoryName = 'analytics' | 'comments' | 'newsletters' | 'ads' | 'memberships' | 'forms';

interface Integration {
	slug: string;
	name: string;
	subname?: string;
	icon: string;
	title: string;
	description: string;
	category: CategoryName;
	color: string;
	component?: Component;
}

export const integrations: Integration[] = [
	//analytics
	// google analytics

	{
		slug: 'google-analytics',
		name: 'Google Analytics',
		icon: '/images/integrations/icons/google-analytics.png',
		title: 'Add Google Analytics to your blog',
		description:
			'Google Analytics is a widely-used analytics service for tracking and reporting website traffic and user behavior.',
		category: 'analytics',
		color: '#e7c565',
		component: GoogleAnalytics
	},

	// cloudflare analytics
	{
		slug: 'cloudflare-analytics',
		name: 'Cloudflare Analytics',
		icon: '/images/integrations/icons/cloudflare-analytics.png',
		title: 'Add Cloudflare Analytics to your blog',
		description:
			"Cloudflare Analytics provides website performance and security insights, integrated with Cloudflare's CDN services. Learn how to integrate Cloudflare Analytics with your Hyvor Blogs blog.",
		category: 'analytics',
		color: '#ffdab8',
		component: CloudflareAnalytics
	},

	// matomo
	{
		slug: 'matomo',
		name: 'Matomo',
		icon: '/images/integrations/icons/matomo.png',
		title: 'Add Matomo Analytics to your blog',
		description:
			'Matomo Analytics is an open-source web analytics platform for tracking and analyzing website visitor data. Learn how to integrate Matomo Analytics with your Hyvor Blogs blog.',
		category: 'analytics',
		color: '#dbe5ff',
		component: Matomo
	},

	//fathom
	// {
	//     slug: 'fathom',
	//     name: 'Fathom',
	//     icon: "/images/integrations/icons/fathom.svg",
	//     title: 'Add Fathom Analytics to your blog',
	//     description: 'Fathom is a simple, privacy-focused analytics platform that offers basic website traffic insights without using cookies or tracking personal data. Learn how to integrate Fathom Analytics with your Hyvor Blogs blog.',
	//     category: 'analytics',
	//     color: '#e4e0fa'
	// },

	//plausible analytics
	// {
	//     slug: 'plausible-analytics',
	//     name: 'Plausible Analytics',
	//     icon: "/images/integrations/icons/plausible-analytics.png",
	//     title: 'Add Plausible Analytics to your blog',
	//     description: 'Plausible is a lightweight, privacy-friendly alternative to Google Analytics, offering simple yet insightful website analytics with a focus on user privacy. Learn how to integrate Plausible Analytics with your Hyvor Blogs blog.',
	//     category: 'analytics',
	//     color: '#eceeff'
	// },

	//simple analytics
	// {
	//     slug: 'simple-analytics',
	//     name: 'Simple Analytics',
	//     icon: "/images/integrations/icons/simple-analytics.svg",
	//     title: 'Add Simple Analytics to your blog',
	//     description: 'Simple Analytics is a privacy-friendly web analytics tool that provides basic website traffic metrics while prioritizing user anonymity and data protection. Learn how to integrate Simple Analytics with your Hyvor Blogs blog.',
	//     category: 'analytics',
	//     color: '#ffd6da'
	// },

	//posthog
	// {
	//     slug: 'posthog',
	//     name: 'Posthog',
	//     icon: "/images/integrations/icons/posthog.svg",
	//     title: 'Add Posthog Analytics to your blog',
	//     description: 'Posthog is an open-source product analytics platform that offers advanced user behavior tracking and insights for web applications. Learn how to integrate Posthog Analytics with your Hyvor Blogs blog.',
	//     category: 'analytics',
	//     color: '#e1e1e1'
	// },

	//zoho analytics
	// {
	//     slug: 'zoho-analytics',
	//     name: 'Zoho Analytics',
	//     icon: "/images/integrations/icons/zoho-analytics.png",
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
		icon: '/images/integrations/icons/hyvor-talk.svg',
		title: 'Add Hyvor Talk Comments to your blog',
		description:
			'Hyvor Talk is a privacy-focused, feature-rich commenting system for websites, offering real-time discussions with spam protection and a user-friendly interface. Learn how to integrate Hyvor Talk Comments with your Hyvor Blogs blog.',
		category: 'comments',
		color: '#ffe6a4',
		component: HyvorTalkComments
	},

	//disqus
	// {
	//     slug: 'disqus',
	//     name: 'Disqus',
	//     icon: "/images/integrations/icons/disqus.png",
	//     title: 'Add Disqus to your blog',
	//     description: 'Disqus is a commenting platform that offers real-time discussions, spam protection, and user engagement features for websites. Learn how to integrate Disqus with your Hyvor Blogs blog. Learn how to integrate Disqus with your Hyvor Blogs blog.',
	//     category: 'comments',
	//     color: '#c9e7ff'
	// },

	//commento
	// {
	//     slug: 'commento',
	//     name: 'Commento',
	//     icon: "/images/integrations/icons/commento.png",
	//     title: 'Add Commento to your blog',
	//     description: 'Commento is a fast, privacy-focused commenting platform that offers real-time discussions and user engagement features for websites. Learn how to integrate Commento with your Hyvor Blogs blog.',
	//     category: 'comments',
	//     color: '#c0c0c0'
	// },

	//getreplybox
	// {
	//     slug: 'getreplybox',
	//     name: 'GetReplyBox',
	//     icon: "/images/integrations/icons/getreplybox.png",
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
	//     icon: "/images/integrations/icons/hyvor-talk.svg",
	//     title: 'Add Hyvor Talk Newsletters to your blog',
	//     description: 'Hyvor Talk is a privacy-focused newsletter platform for websites and blogs. Learn how to integrate Hyvor Talk Newsletters with your Hyvor Blogs blog.',
	//     category: 'newsletters',
	//     color: '#ffe6a4'
	// },

	//convertkit
	// {
	//     slug: 'convertkit',
	//     name: 'ConvertKit',
	//     icon: "/images/integrations/icons/convertkit.svg",
	//     title: 'Add ConvertKit to your blog',
	//     description: 'ConvertKit is an email marketing platform that offers advanced automation and segmentation features for growing your newsletter list. Learn how to integrate ConvertKit with your Hyvor Blogs blog.',
	//     category: 'newsletters',
	//     color: '#fee4e5'
	// },

	//mailchimp
	{
		slug: 'mailchimp',
		name: 'Mailchimp',
		icon: '/images/integrations/icons/mailchimp.svg',
		title: 'Add Mailchimp to your blog',
		description:
			'Mailchimp is an all-in-one marketing platform that offers advanced email marketing and automation features for growing your newsletter list. Learn how to integrate Mailchimp with your Hyvor Blogs blog.',
		category: 'newsletters',
		color: '#fae2c4',
		component: Mailchimp
	},

	//emailoctopus
	// {
	//     slug: 'emailoctopus',
	//     name: 'EmailOctopus',
	//     icon: "/images/integrations/icons/emailoctopus.svg",
	//     title: 'Add EmailOctopus to your blog',
	//     description: 'EmailOctopus is an email marketing platform that offers advanced automation and segmentation features for growing your newsletter list. Learn how to integrate EmailOctopus with your Hyvor Blogs blog.',
	//     category: 'newsletters',
	//     color: '#ddd8f2'
	// },

	//mossend
	// {
	//     slug: 'moosend',
	//     name: 'Moosend',
	//     icon: "/images/integrations/icons/moosend.png",
	//     title: 'Add Moosend to your blog',
	//     description: 'Moosend is an email marketing platform that offers advanced automation and segmentation features for growing your newsletter list. Learn how to integrate Moosend with your Hyvor Blogs blog.',
	//     category: 'newsletters',
	//     color: '#e9dadf'
	// },

	//mailerlite
	// {
	//     slug: 'mailerlite',
	//     name: 'MailerLite',
	//     icon: "/images/integrations/icons/mailerlite.png",
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
	//     icon: "/images/integrations/icons/hyvor-talk.svg",
	//     title: 'Add Hyvor Talk Memberships to your blog',
	//     description: 'Hyvor Talk is a privacy-focused, feature-rich memberships platform. Learn how to integrate Hyvor Talk Memberships with your Hyvor Blogs blog.',
	//     category: 'memberships',
	//     color: '#ffe6a4'
	// },

	//memberstack
	// {
	//     slug: 'memberstack',
	//     name: 'Memberstack',
	//     icon: "/images/integrations/icons/memberstack.svg",
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
	//     icon: "/images/integrations/icons/memberspace.png",
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
		icon: '/images/integrations/icons/google-forms.png',
		title: 'Add Google Forms to your blog',
		description:
			'Google Forms is a widely-used web forms service offered by Google for creating and managing online forms and surveys. Learn how to integrate Google Forms with your Hyvor Blogs blog.',
		category: 'forms',
		color: '#d1b8e7',
		component: GoogleForms
	},

	//microsft forms
	// {
	//     slug: 'microsoft-forms',
	//     name: 'Microsoft Forms',
	//     icon: "/images/integrations/icons/microsoft-forms.png",
	//     title: 'Add Microsoft Forms to your blog',
	//     description: 'Microsoft Forms is a widely-used web forms service offered by Microsoft for creating and managing online forms and surveys. Learn how to integrate Microsoft Forms with your Hyvor Blogs blog.',
	//     category: 'forms',
	//     color: '#adeff2'
	// },

	//typeform
	// {
	//     slug: 'typeform',
	//     name: 'Typeform',
	//     icon: "/images/integrations/icons/typeform.png",
	//     title: 'Add Typeform to your blog',
	//     description: 'Typeform is an interactive form and survey builder known for its intuitive, conversational design, allowing users to create engaging, customizable forms with ease. Learn how to integrate Typeform with your Hyvor Blogs blog.',
	//     category: 'forms',
	//     color: '#e5e5e5'
	// },

	//wufoo
	// {
	//     slug: 'wufoo',
	//     name: 'Wufoo',
	//     icon: "/images/integrations/icons/wufoo.png",
	//     title: 'Add Wufoo to your blog',
	//     description: 'Wufoo is a web forms service that offers a user-friendly form builder with advanced customization and integration capabilities. Learn how to integrate Wufoo with your Hyvor Blogs blog.',
	//     category: 'forms',
	//     color: '#ffd1cb'
	// },

	//jotform
	// {
	//     slug: 'jotform',
	//     name: 'Jotform',
	//     icon: "/images/integrations/icons/jotform.png",
	//     title: 'Add Jotform to your blog',
	//     description: 'JotForm is a versatile online form builder offering a wide range of templates, customization options, and integrations for creating forms, surveys, and registrations. Learn how to integrate Jotform with your Hyvor Blogs blog.',
	//     category: 'forms',
	//     color: '#ffe2a6'
	// },

	//formsite
	// {
	//     slug: 'formsite',
	//     name: 'Formsite',
	//     icon: "/images/integrations/icons/formsite.png",
	//     title: 'Add Formsite to your blog',
	//     description: 'Formsite is a user-friendly form builder with advanced features such as conditional logic, payment integration, and custom branding for creating professional forms and surveys.',
	//     category: 'forms',
	//     color: '#c4e3ff'
	// },

	//paperform
	// {
	//     slug: 'paperform',
	//     name: 'Paperform',
	//     icon:  "/images/integrations/icons/paperform.png",
	//     title: 'Add Paperform to your blog',
	//     description: 'Paperform is an elegant online form builder that combines the simplicity of a form with the richness of a document, allowing for beautiful, customizable forms and surveys. Learn how to integrate Paperform with your Hyvor Blogs blog.',
	//     category: 'forms',
	//     color: '#e2e2e2'
	// },

	//zoho forms
	// {
	//     slug: 'zoho-forms',
	//     name: 'Zoho Forms',
	//     icon: "/images/integrations/icons/zoho-forms.png",
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
	//     icon: "/images/integrations/icons/facebook-pixel.png",
	//     title: "Add Facebook Pixel to your blog",
	//     description: 'Facebook Pixel is a tracking tool by Facebook for measuring and optimizing ad performance, helping businesses understand user behavior across their websites and target audiences effectively. Learn how to integrate Facebook Pixel with your Hyvor Blogs blog.',
	//     category: 'ads',
	//     color: "#e0eaff"
	// },

	//google adsense
	{
		slug: 'google-adsense',
		name: 'Google Adsense',
		icon: '/images/integrations/icons/google-adsense.svg',
		title: 'Add Google AdSense to your blog',
		description:
			'Google AdSense is a widely-used advertising platform by Google that allows website owners to monetize their content by displaying targeted ads to visitors. Learn how to integrate Google AdSense with your Hyvor Blogs blog.',
		category: 'ads',
		color: '#d9e1ef',
		component: GoogleAdsense
	}

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
	name: CategoryName;
	title: string;
	integrations: Integration[];
}

export const categories: Catergory[] = [
	{
		name: 'analytics',
		title: 'Analytics',
		integrations: integrations.filter((integration) => integration.category === 'analytics')
	},
	{
		name: 'comments',
		title: 'Comments',
		integrations: integrations.filter((integration) => integration.category === 'comments')
	},
	{
		name: 'newsletters',
		title: 'Newsletters',
		integrations: integrations.filter((integration) => integration.category === 'newsletters')
	},

	{
		name: 'ads',
		title: 'Ads',
		integrations: integrations.filter((integration) => integration.category === 'ads')
	},

	// {
	//     name: 'memberships',
	//     title: 'Memberships',
	//     integrations: integrations.filter(integration => integration.category === 'memberships')
	// },

	{
		name: 'forms',
		title: 'Forms',
		integrations: integrations.filter((integration) => integration.category === 'forms')
	}
];

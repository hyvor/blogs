import webflowIcon from './icons/webflow.png';
import bubbleIcon from './icons/bubble.png';
import squarespaceIcon from './icons/squarespace.png';
import wixIcon from './icons/wix.png';
import shopifyIcon from './icons/shopify.png';
import wordpressIcon from './icons/wordpress.png';

interface Platform {
    slug: string;
    name: string;
    title: string;
    subtitle: string;
    ctaTitle: string;
    icon: string;
}

export const platforms : Platform[] = [
    {
        slug: 'squarespace',
        name: "Squarespace",
        title: "Add a blog to your Squarespace site",
        subtitle: 'Improve your Squarespace site with a fast and SEO-friendly blog. No coding required. Host on a custom domain or subdirectory.',
        ctaTitle: "Ready to add a blazing fast, SEO-friendly blog to your Squarespace site?",
        icon: squarespaceIcon,
    },
    {
        slug: 'wix',
        name: "Wix",
        title: "Add a blog to your Wix site",
        subtitle: 'Set up a beautiful blog for your Wix site in minutes. No coding required. Host on a custom domain or subdirectory.',
        ctaTitle: "Ready to add a blazing fast, SEO-friendly blog to your Wix site?",
        icon: wixIcon,
    },
    {
        slug: 'shopify',
        name: "Shopify",
        title: "Add a blog to your Shopify shop",
        subtitle: 'Add a fast and SEO-friendly blog to your Shopify shop in minutes. No coding required. Host on a custom domain or subdirectory.',
        ctaTitle: "Ready to add a blazing fast, SEO-friendly blog to your Shopify shop?",
        icon: shopifyIcon,
    },
    {
        slug: 'webflow',
        name: 'Webflow',
        title: 'Add a blog to your Webflow site',
        subtitle: 'Set up a beautiful blog for your Webflow site in minutes. No coding required. Host on a custom domain or subdirectory.',
        ctaTitle: 'Ready to add a blazing fast, SEO-friendly blog to your Webflow site?',
        icon: webflowIcon,
    },
    {
        slug: 'bubble',
        name: "Bubble",
        title: "Add a blog to your Bubble app",
        subtitle: 'Add a fast and SEO-friendly blog to your Bubble app in minutes. No coding required. Host on a custom domain or subdirectory.',
        ctaTitle: "Ready to add a blazing fast, SEO-friendly blog to your Bubble app?",
        icon: bubbleIcon,
    },
    {
        slug: 'wordpress',
        name: "WordPress",
        title: "Add a blog to your WordPress site",
        subtitle: 'Set up a beautiful blog for your WordPress site in minutes. No coding required. Host on a custom domain or subdirectory.',
        ctaTitle: "Ready to add a blazing fast, SEO-friendly blog to your WordPress site?",
        icon: wordpressIcon,
    }
];
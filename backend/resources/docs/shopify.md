# Shopify Integration

Hyvor Blogs integrates with [Shopify](https://www.shopify.com/) to allow Shopify shop owners to add a blog to the same domain as their shop. If you have a `myshop.com` domain, you can add a blog to `myshop.com/a/blog` or `myshop.com/community/news` to have your blog and shop in the same domain and improve SEO.

> If you want to add a blog to subdomain (e.g. `blog.myshop.com`), sign up for Hyvor Blogs normally and follow the [custom domain](/docs/custom-domain) guide.

### Installation {#installation}

You can install the [Hyvor Blogs Shopify App](https://apps.shopify.com/) from the Shopify App Store. A new blog will be created for you, and you will be directed to our **Console**, where you can manage your blog. If you chose to upgrade, the subscription charges will be handled by Shopify. Same pricing shown on our [pricing](/pricing) page will be applied.

### Blog URL {#url}

By default, your blog URL will have the **myshop.myshopify.com/a/blog** format. To customize it,

* Log into your Shopify store admin panel
* **Apps** &rarr; **App and sales channel settings** &rarr; **Hyvor Blogs**
* In **App Proxy**, click **Customize URL**, and customize as you need
* Save changes and verify by visiting the new URL
* Finally, update the blog URL in the Hyvor Blogs **Console** &rarr; **Settings** &rarr; **Hosting** &rarr; **Self-Hosting URL**

> Technical Note: Hyvor Blogs uses Shopify's [App Proxies](https://shopify.dev/apps/online-store/app-proxies) to serve the blog.

### Updating the Theme {#theme}

[Shopify themes](https://themes.shopify.com/) and [Hyvor Blogs themes](https://blogs.hyvor.com/themes) are completely different. When you update your theme files or change your theme from our Console, you will be editing the blog theme. This is how you change the appearance in Hyvor Blogs. This action does not change any of Shopify theme files. See [theme development](themes-overview) documentation for more information on how themes work in Hyvor Blogs.
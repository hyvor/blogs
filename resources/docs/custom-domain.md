# Custom Domain

We highly encourage you to set up a custom domain for your blog. It allows you to "own" your content completely without locking into us. It will also help you create your own brand.

* [Choosing a domain](#choosing)
* [Setting up DNS Records](#dns)
* [Setting up Custom Domain](#settings)

## Choosing a domain {#choosing}

Your blog can be hosted on an primary domain (myblog.com) or on a subdomain (blog.mywebsite.com). Either way, you first need to own a domain name. Searching "buy a domain name" will direct you in the right direction. Note that buying a domain involves additional costs not included in the Hyvor Blogs subscription.

## Setting up DNS Records {#dns}

Once you have chosen the domain or subdomain, you have to set up an A record in DNS settings of the domain. If you are not sure how to do that, check documentation of the DNS host (Search "How to set up A record in {your provider}").

Create an A record with the following values.

* Host
  * `@` if you want to use the primary domain (myblog.com) for the blog
  * Subdomain (ex: `blog`) if you want to use a subdomain (`blog.mywebsite.com`)
* Value (IP Address): **116.202.185.2**

> You may  alternatively use a CNAME record that point to `yoursubdomain.hyvorblogs.io`.

## Setting up Custom Domain {#settings}

Once you have set up DNS, you have to update your blog settings.

* Go to **[Console](/console) &rarr; Settings &rarr; Hosting**
* Change **Hosting on/at** setting to **Custom Domain**.
* Type the full domain name (`myblog.com` or `blog.mywebsite.com`) in the custom domain field, and save. Do not add `https://` or anything else than the domain name.

That should be all! Visit the custom domain and see if everything is working correctly. HB will auto generate a SSL certificate for your domain.

## Troubleshooting

* If your blog with custom domain is loading infinitely, please make sure you do not have any other `A` or `AAAA` records with the same hostname as your custom domain.
* If you are using Cloudflare, make sure to disable Cloudflare proxy for your custom domain. You can do this by clicking the orange cloud icon in the DNS settings of Cloudflare.

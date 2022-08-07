# Embedding a Blog

Let's assume you already have a website built with a website builder like Wix, Squarespace, or Webflow. And, you want to have a blog at `/blog` path of your website. But, website builders usually do not provide custom controllers for routes. But, you can use embedding to have a blog inside your website.

## How Embedding Works

First, you should add our embedding script to a page of your website (Ex: `/blog`). Your homepage will be rendered at `/blog`. Other pages will be rendered using URL query string. For example, if your blog has a `hello-world` post, it will be rendered at `/blog?p=hello-world`. Our embed script determines what page to display and dynamically loads the page contents.

The blog is rendered inside a [Web Component](https://developer.mozilla.org/en-US/docs/Web/Web_Components) using Shadow DOM. Therefore, styles are not leaked from your website to the blog or vice versa. All our [official themes](/themes) are designed keeping embedding in mind.

## Advantages

* Embedding allows you to keep your site header and footer. The header and footer of the blog will also be shown, but you can 
* Embedding is the only way to add a blog to a website when you do not have access to the back-end (ex: website builders).
* It allows you to have your blog in the same domain as the primary site.

## Disadvantages

* Embedding is slightly slower than direct rendering because of Javascript usage.
* Query-based URLs are not the best-looking.
* SEO robots may interpret the dynamically created page content and meta tags differently.

## SEO

We are yet to do our own tests on the SEO impacts of embedding. But, according to research of other's most SEO bots are capable of reading your content, hence there will not be any affect to SEO.

Having your blog in the primary domain rather than a subdomain is usually a plus for SEO.
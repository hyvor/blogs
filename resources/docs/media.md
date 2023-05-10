# Media

> This documentation is still in progress.

* [Images](#images)
  * [WebP Usage](#webp-usage)
  * [Responsive Images](#responsive-images)

## Images {#images}

"One image can be worth a thousand words". Images are a great way to improve your blog posts and make them more appealing and informative to your users. However, images can also be a source of performance issues if not used properly. Hyvor Blogs automatically optimizes images for you when possible.

### WebP Usage {#webp-usage}

[WebP](https://en.wikipedia.org/wiki/WebP) images are 25-34% smaller than JPEG/PNG images of the same quality. It is recommended nowadays to use WebP in websites whenever possible as all modern browsers [support WebP images](https://caniuse.com/webp). You can upload JPEG and PNG as usual in your posts. Hyvor Blogs will serve them in WebP format to your users via `/media` of your blog. You don't have to do anything.

> Note that the extension in the URL not change (ex: `/media/image.jpg` or `/media/image.png`), but the image will be served as WebP with correct headers. You can verify this by checking your page in [PageSpeed Insights](https://pagespeed.web.dev/).
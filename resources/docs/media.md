# Media

* [Images](#images)
  * [WebP Format](#webp)
  * [Responsive Images](#responsive-images)

## Images {#images}

"One image can be worth a thousand words". Images are a great way to improve your blog posts and make them more appealing and informative to your users. However, images can also be a source of performance issues if not used properly. Hyvor Blogs automatically optimizes images for you when possible.

### WebP Format {#webp}

[WebP](https://en.wikipedia.org/wiki/WebP) images are 25-34% smaller than JPEG/PNG images of the same quality. It is recommended nowadays to use WebP in websites whenever possible as all modern browsers [support WebP images](https://caniuse.com/webp). You can upload JPEG and PNG as usual in your posts. Hyvor Blogs will serve them in WebP format to your users via `/media` of your blog. You don't have to do anything.

> Note that the extension in the URL not change (ex: `/media/image.jpg` or `/media/image.png`), but the image will be served as WebP with correct headers. You can verify this by checking your page in [PageSpeed Insights](https://pagespeed.web.dev/).

### Responsive Images

Hyvor Blogs handles responsive images for you. Here's how it works.

Images (PNG, JPEG, WebP only) uploaded to `/media` can be automatically resized by appending `/{width}w` to the URL.

* `/media/image.jpg` - Original image
* `/media/image.jpg/100w` - Resized to maximum width 100px
* `/media/image.jpg/750w` - Resized to maximum width 750px

We use this resizing feature and [srcset](https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images) (supported by all modern browsers) to make images in your posts responsive. For example, if you upload a 1000px image to a post, a smaller version of that image will be served to users with smaller screens.

These are the sizes we use:

* 500w
* 750w
* 1000w
* 1500w

For example, if you upload a 1250px image to a post, the HTML code would look like this:

```html
<img 
    src="/media/image.jpg" 
    srcset="
        /media/image.jpg/500w 500w, 
        /media/image.jpg/750w 750w, 
        /media/image.jpg/1000w 1000w,
        /media/image.jpg 1250w
    "
    alt="Image"
/>
```

Then, the browser will decide the best image version to load based on the screen size and the device pixel ratio (DPR).

> Note that responsive images are not supported for externally hosted images (ex: images from Unsplash)
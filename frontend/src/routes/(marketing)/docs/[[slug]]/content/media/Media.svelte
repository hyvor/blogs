<script>
	import { Callout, CodeBlock } from '@hyvor/design/components';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
</script>

<h1>Media</h1>

<p>
	"Media" refers to the files you upload in your blog. It can be images, videos, audio, or any other
	file. Visit <strong>Tools &rarr; Media Library</strong> to manage your blog media.
</p>

<h2 id="images">Images</h2>

<p>
	"A picture is worth a thousand words". Adding pictures to your blog posts is a great way to make
	them more interesting and informative. But be careful – using too many or large images can slow
	down your website. Hyvor Blogs automatically optimizes images for you when possible.
</p>

<p>
	You can upload images in post editor, in the media library, or in blog settings such as logo,
	favicon, etc. The following image formats are supported:
</p>

<ul>
	<li>PNG - <code>.png</code></li>
	<li>
		JPEG - <code>.jpg</code>, <code>.jpeg</code>, <code>.jfif</code>, <code>.pjpeg</code>,
		<code>.pjp</code>
	</li>
	<li>GIF - <code>.gif</code></li>
	<li>APNG - <code>.apng</code></li>
	<li>AVIF - <code>.avif</code></li>
	<li>SVG - <code>.svg</code></li>
	<li>WebP - <code>.webp</code></li>
</ul>

<h3 id="webp">Automatic WebP Conversion</h3>

<p>
	<a href="https://en.wikipedia.org/wiki/WebP" rel="nofollow" target="_blank">WebP</a> images are 25-34%
	smaller than JPEG/PNG images of the same quality. It is recommended nowadays to use WebP in websites
	whenever possible as all modern browsers support WebP images. You can upload JPEG and PNG as usual
	in your posts. Hyvor Blogs will automatically convert them to WebP images on the fly. You don't need
	to do anything.
</p>

<Callout type="info">
	{#snippet icon()}
		<IconLightbulb />
	{/snippet}
	Note that the extension in the URL will not change (ex: <code>/media/image.jpg</code> or
	<code>/media/image.png</code>), but the image will be served as WebP with correct HTTP headers.
	You can verify this by checking your page in
	<a href="https://pagespeed.web.dev/" target="_blank" rel="nofollow">PageSpeed Insights</a>.
</Callout>

<h3 id="responsive-images">Responsive Images</h3>

<p>
	Hyvor Blogs handles responsive images for you. Here's how it works. Images (PNG, JPEG, WebP only)
	uploaded to the media library can be automatically resized by appending <code>{`/{width}w`}</code>
	to the URL.
</p>

<ul>
	<li>
		<code>/media/image.png</code> - Original image
	</li>
	<li>
		<code>/media/image/100w.png</code> - Resized to maximum width 100px
	</li>
	<li>
		<code>/media/image/750w.png</code> - Resized to maximum width 750px
	</li>
</ul>

<p>
	We use this resizing feature and <a
		href="https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images"
		target="_blank"
		rel="nofollow">srcset</a
	> (supported by all modern browsers) to make images in your posts responsive. These are the sizes we
	use:
</p>

<ul>
	<li>
		<code>500w</code>
	</li>
	<li>
		<code>750w</code>
	</li>
	<li>
		<code>1000w</code>
	</li>
	<li>
		<code>1500w</code>
	</li>
</ul>

<p>For example, if you upload a 1250px image to a post, the HTML code would look like this:</p>

<CodeBlock
	code={`
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
`}
/>

<p>
	Then, the browser will decide the best image version to load based on the screen size and the
	device pixel ratio (DPR).
</p>

<p>
	Note that this feature is only available for images uploaded to the media library. Externally
	hosted images will not be resized.
</p>

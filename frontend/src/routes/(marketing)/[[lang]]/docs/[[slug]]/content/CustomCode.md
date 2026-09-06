<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import IconBrush from '@hyvor/icons/IconBrush';
	import IconPencil from '@hyvor/icons/IconPencil';

	import { Callout, Divider, Tag } from '@hyvor/design/components';
</script>

# Custom Code

You can add custom code to customize your blog, add styles, or integrate third-party services like analytics.

- All custom code can contain HTML or Twig code and has access to [theme variables](/docs/themes-templates#variables).
- Head code will be added before the `</head>` tag.
- Foot code will be added before the `</body>` tag.
- There are three ways to add custom code to your blog:
  - **Blog** - add code to all pages of your blog.
  - **Post** - add code to a specific post.
  - **Tag** - add code to all posts with a specific tag.
- Custom code is added in the **Blog -> Post -> Tag** order.

<h2 id="blog">1. Blog custom code</h2>

You can add custom code to the whole blog in **Settings → Custom Code**. This way, the custom code will be added to all templates in your blog. This is useful for adding services like analytics.

<DocsImage src="/images/docs/custom-code/custom-code-blog.png" alt="Custom code in blog settings" />

<h2 id="post">2. Post custom code</h2>

You can also add custom code a specific post only in **advanced settings in the post editor**. The custom code will then only be added to that post.

<DocsImage src="/images/docs/custom-code/custom-code-post.png" alt="Custom code in post settings" />

<h2 id="tag">3. Tag custom code</h2>

You can also define custom code for a tag in **Settings → Tags → <IconPencil /> → Custom Code**. Then, this custom code will be added to the posts that has this tag (not to the tag page itself).

For example, posts that have SVG animations will need an additional Javascript library to play SVG animations. You can assign a "svg" tag to those posts and link to the Javascript library in the custom code of that tag.

<DocsImage src="/images/docs/custom-code/custom-code-tag.png" alt="Custom code in tag settings" />

<Divider margin={30} color="var(--border)" />

<Callout type="info">
	{#snippet icon()}
		<IconBrush />
	{/snippet}
	You can also <a href="/docs/theme#editing">edit your theme</a> to add custom code to your blog.
</Callout>

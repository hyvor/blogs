<script lang="ts">
    import { DocsImage } from "@hyvor/design/marketing";
    
    import customCodeBlog from './custom-code-blog.png'
    import customCodePost from './custom-code-post.png'
    import customCodeTag from './custom-code-tag.png'

	import { IconBrush, IconPencil } from "@hyvor/icons";
	import { Callout, Divider, Tag } from "@hyvor/design/components";
</script>

<h1>Custom Code</h1>

<p>
    You can add custom code to customize your blog, add styles, or integrate third-party services like analytics.
</p>

<ul>
    <li>
        All custom code can contain HTML or Twig code and has access to <a href="/docs/themes-templates#variables">theme variables</a>.
    </li>
    <li>
        Head code will be added before the <code>{'</head>'}</code> tag.
    </li>
    <li>
        Foot code will be added before the <code>{'</body>'}</code> tag.
    </li>
    <li>
        There are three ways to add custom code to your blog:
        <ul>
            <li>
                <strong>Blog</strong> - add code to all pages of your blog.
            </li>
            <li>
                <strong>Post</strong> - add code to a specific post.
            </li>
            <li>
                <strong>Tag</strong> - add code to all posts with a specific tag.
            </li>
        </ul>
    </li>
    <li>
        Custom code is added in the <strong>Blog -{'>'} Post -{'>'} Tag</strong> order.
    </li>
</ul>

<h2 id="blog">
    1. Blog custom code
</h2>

<p>
    You can add custom code to the whole blog in <strong>Settings → Custom Code</strong>. This way, the custom code will be added to all templates in your blog. This is useful for adding services like <a href="/docs/services">analytics</a>.
</p>

<DocsImage src={customCodeBlog} alt="Custom code in blog settings" />

<h2 id="post">
    2. Post custom code
</h2>

<p>
    You can also add custom code a specific post only in <strong>advanced settings in the post editor</strong>. The custom code will then only be added to that post.
</p>

<DocsImage src={customCodePost} alt="Custom code in post settings" />

<h2 id="tag">
    3. Tag custom code
</h2>

<p>
    You can also define custom code for a tag in <strong>Settings → Tags → <IconPencil /> → Custom Code</strong>. Then, this custom code will be added to the posts that has this tag (not to the tag page itself).
</p>

<p>
    For example, posts that have SVG animations will need an additional Javascript library to play SVG animations. You can assign a "svg" tag to those posts and link to the Javascript library in the custom code of that tag.
</p>

<DocsImage src={customCodeTag} alt="Custom code in tag settings" />

<Divider margin={30} color="var(--border)" />

<Callout type="info">
    <IconBrush slot="icon" />
    You can also <a href="/docs/theme#editing">edit your theme</a> to add custom code to your blog.
</Callout>
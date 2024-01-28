<script>
	import { Callout, Link } from "@hyvor/design/components";
	import { blogStore } from "../../../lib/stores/blogStore";
	import { consoleUrlWithBlog } from "../../../lib/consoleUrl";

</script>

<div>
<Callout type="info">
    Media settings are coming soon with the following features.
    <ul>
        <li>
            Adjust how your media (ex: images) files are served.
        </li>
        <li>
            Set up S3 for storing your media files.
        </li>
    </ul>
    Stay tuned!
</Callout>

<p style="font-size:14px;color:var(--text-light)">
    Media library is now moved to <Link href={consoleUrlWithBlog('/tools/media')}>Tools &rarr; Media Library</Link>.
</p>
</div>

<style>
    div {
        padding: 30px;
    }
</style>
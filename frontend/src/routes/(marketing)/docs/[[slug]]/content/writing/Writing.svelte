<script lang="ts">
    import postsPagesImg from './posts-pages-console.png';
</script>

<h1>Writing</h1>

<p>
    Let's learn how to use the Hyvor Blogs Editor and publish your <strong>first post!</strong>
</p>

<!-- <ul>
    <li><a href="#posts-pages">Posts & Pages</a></li>
    <li>
        <a href="#editor">Editor</a>
        <ul>
            <li>
                <a href="#inline">Inline Styles</a> (bold, italic, etc.)
            </li>
            <li>
                <a href="#links">Links</a>
            </li>
            <li>
                <a href="#images">Blocks</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="#post-metadata">Post Metadata</a> (authors, tag, custom code)
    </li>
    <li>
        <a href="#post-status">Post Status</a>
        <ul>
            <li><a href="#publishing">Publishing</a></li>
            <li><a href="#scheduling">Scheduling</a></li>
            <li><a href="#unpublishing">Unpublishing</a></li>
            <li><a href="#deleting">Deleting</a></li>
        </ul>
    </li>
    <li>
        <a href="#other-guides">Other Guides</a>
        <ul>
            <li><a href="#auto-saving">Auto-saving & Post History</a></li>
            <li><a href="#editing-published">Editing a published post</a></li>
            <li><a href="#multi-language">Multi-language posts</a></li>
        </ul>
    </li>
    <li><a href="#seo-analysis">SEO Analysis</a></li>
    <li><a href="#link-analysis">Link Analysis</a></li>
    <li><a href="#gpt-writing">GPT Writing</a></li>
</ul> -->

<h2 id="posts-pages">
    Posts vs Pages
</h2>

<p>
    Hyvor Blogs supports two types of content: <strong>posts</strong> and <strong>pages</strong>. In most cases, you will be using posts. Pages are used for static content like About, Contact, etc.
</p>

<p>
    <strong>Posts</strong>:
</p>

<ul>
    <li>
        are the main part of your blog, where you share your ideas, thoughts, and stories.
    </li>
    <li>
        appear in the index page or other collection pages
    </li>
    <li>
        can be created, edited, and deleted at Console → Posts
    </li>
    <li>
        have authors and tags
    </li>
</ul>

<p>
    <strong>Pages</strong>:
</p>

<ul>
    <li>
        contain static information
    </li>
    <li>
        do not appear in the index page.
    </li>
    <li>
        usually is linked to in header or footer navigations.
    </li>
    <li>
        can create, edit, and delete pages at Console → Pages
    </li>
    <li>
        do not have authors or tags
    </li>
</ul>

<p>
    <strong>Both</strong>:
</p>

<ul>
    <li>
        are uniquely identified by the <strong>slug</strong>
    </li>
    <li>
        by default have <code>{`/{slug}`}</code> permalink. See <a href="/docs/routes#permalinks">this</a> if you want to change post/page permalinks, for example to include year and month in the URL.
    </li>
</ul>

<p>
    <img src={postsPagesImg} alt="Posts & Pages in the Console" style="width:400px" />
</p>
<script lang="ts">
	import { Callout, CodeBlock, Divider, Table, TableRow } from "@hyvor/design/components";


</script>

<h1 id="api-data">API Data</h1>

<p>The Data API returns the public data of the blog.</p>

<ul>
    <li>No API keys are required.</li>
    <li>All responses are in the JSON format.</li>
    <li>All endpoints use the HTTP <code>GET</code> method.</li>
    <li>The base path is: <code>{`https://blogs.hyvor.com/api/data/v0/{subdomain}`}</code></li>
    <li>For example, if your blog is at <code>https://example.hyvor.com</code>, the base path is <code>https://blogs.hyvor.com/api/data/v0/example</code></li>
        <ul>
            <li>Replace <code>{`{subdomain}`}</code> with the subdomain of your blog.</li>
        </ul>
</ul>

<Callout type="info">
    <p>In addition to calling the Data API via HTTP, it is possible call it within template files using the Twig <a href="/docs/themes-templates#fetch-data">data() function</a>. It is the preferred method if you want data to render some UI (Ex: recent posts section) in your blog, because the <code>{`data()`}</code> function calls the Data API internally at the time of rendering the template, eliminating the need for additional HTTP requests.</p>
</Callout>

<h2 id="endpoints">Endpoints</h2>

<p><b>Single-object</b></p>
<ul>
    <li><code>/post</code> - a post/page</li>
    <li><code>/tag</code></li>
    <li><code>/author</code></li>
    <li><code>/blog</code>- blog settings</li> 
</ul>

<p><b>Multi-object</b></p>

<ul>
    <li><code>/posts</code></li>
    <li><code>/posts/search</code> - search posts</li>
    <li><code>/tags</code></li>
    <li><code>/authors</code></li>
</ul>

<h2 id="response">Response</h2>
<p>For single-object endpoints, the response is an object. For example, <code>/post</code> endpoint returns a <code>Post</code> object (See below for object definitions).</p>

<CodeBlock language="yaml" code={`
// A Post Object
{
    "id": 1000,
    "slug": "post",
    ...
}
`} />

<p>For multi-object endpoints, the response looks like this:</p>
<CodeBlock language="yaml" code={`
{
    "data": [{}, {}], // array of objects
    "pagination": {} // a Pagination object
}
`} />

<h2 id="request">Request</h2>

<h3 id="single-object">Single-Object endpoints</h3>

<p>For <code>/post</code>, <code>/tag</code>, and <code>/author</code></p>

<Table columns="1fr 2fr 1fr" hover>
    <TableRow head>
        <div>Param</div>
        <div>Description</div>
        <div>Type</div>
    </TableRow>

    <TableRow>
        <div><code>id</code></div>
        <div>id of the object</div>
        <div><code>integer</code></div>
    </TableRow>

    <TableRow>
        <div>slug</div>
        <div>slug of the object</div>
        <div>string</div>
    </TableRow>

    <TableRow>
        <div><code>language</code></div>
        <div>See <a href="/docs/api-data#language">language param</a></div>
        <div>string</div>
    </TableRow>

    <TableRow>
        <div><code>keys</code></div>
        <div>See <a href="/docs/api-data#keys">keys param</a></div>
        <div>string</div>
    </TableRow>
</Table>

<Callout type="info">
    <p>Either the <code>id</code> or the <code>slug</code> is required for those endpoints.</p>
</Callout>

<p>The <code>/blog</code> endpoint only takes <code>language</code> and <code>keys</code> as an input.</p>

<h3 id="multi-object">Multi-Object endpoints</h3>

<p><code>/posts</code>, <code>/posts/search</code>, <code>/tags</code>, and <code>/authors</code></p>

<Table columns="1fr 2fr 1fr 1fr" hover>
    <TableRow head>
        <div>Param</div>
        <div>Description</div>
        <div>Type</div>
        <div>Default</div>
    </TableRow>

    <TableRow>
        <div><code>language</code></div>
        <div>See <a href="/docs/api-data#language">language param</a></div>
        <div>string</div>
        <div></div>
    </TableRow>

    <TableRow>
        <div><code>limit</code></div>
        <div>See <a href="/docs/api-data#limit">limit param</a></div>
        <div>integer</div>
        <div>25</div>
    </TableRow>

    <TableRow>
        <div><code>page</code></div>
        <div>See <a href="/docs/api-data#page">page param</a></div>
        <div>integer</div>
        <div>1</div>
    </TableRow>

    <TableRow>
        <div><code>filter</code></div>
        <div>See <a href="/docs/api-data#filter">filter param</a></div>
        <div>string</div>
        <div>""</div>
    </TableRow>

    <TableRow>
        <div><code>sort</code></div>
        <div>See <a href="/docs/api-data#sort">sort param</a></div>
        <div>string</div>
        <div>[VARIES]</div>
    </TableRow>

    <TableRow>
        <div><code>keys</code></div>
        <div>See <a href="/docs/api-data#keys">keys param</a></div>
        <div>string</div>
        <div></div>
    </TableRow>
</Table>

<p>The <code>/posts/search</code> endpoint has a required <code>search</code> param in addition to the above params.</p>

<Table columns="1fr 2fr 1fr 1fr" hover>
    <TableRow head>
        <div>Param</div>
        <div>Description</div>
        <div>Type</div>
        <div>Default</div>
    </TableRow>

    <TableRow>
        <div><code>search</code></div>
        <div>value to search</div>
        <div>string</div>
        <div></div>
    </TableRow>
</Table>

<h4 id="language">1. <code>language</code> param</h4>

<p>If your blog has <a href="/docs/languages">multiple languages</a>, you can set the <code>language</code> param to a language code (ex: <code>en</code>, <code>fr</code>) of a language in your blog. If this param is not provided, the primary language of the blog is used. That language will be used to localize strings in posts, authors, tags, and the blog.</p>

<Callout type="info">
    <p><b>Note</b>: There is an important distinction between posts (<code>/post</code>, <code>/posts</code>, and <code>/posts/search</code>) and other endpoints when using languages. Let's say you have two languages in your blog: <code>en</code> (primary) and <code>fr</code>. If you call the <code>/posts</code> endpoint with language the language code <code>fr</code>, <b>only the posts that have a</b> <code>fr</code> <b>variant</b> will be returned. However, in other endpoints (authors, tags), all records will be returned regardless of they have a <code>fr</code> variant or not. Missing translations will be filled with primary language strings.

        The reason is that, when someone visits your blog's <code>/fr</code> index page, we only want to show the posts that are translated into French. We do not want to "fallback" post contents. However, fallbacking author/tags data is fine in most cases. <br>
        <Divider />
        In other words, <code>language</code> in post(s) endpoints works as a filter, while it works as a translator in other endpoints.</p>
</Callout>

<h4 id="limit">2. <code>limit</code> param</h4>

<p>The <code>limit</code> param can be used to limit the number of records returned in multi-object endpoints. The default is <code>25</code>. Max is <code>250</code>.</p>

<h4 id="page">3. <code>page</code> param</h4>
<p>The <code>page</code> param can be used to paginate results. This works in combination with the limit param. The default value is <code>1</code>.</p>
<CodeBlock code={`
To get the first 20 results:
/posts?limit=20

To get the next 20 results (page 2):
/posts?limit=20&page=2
`} />

<h4 id="filter">4. <code>filter</code> param</h4>
<p>Example: <code>{`(published_at > 1639665890 & published_at < 1639695890) | is_featured=true`}</code></p>
<p>Our Data API uses <a href="https://github.com/hyvor/laravel-filterq">Laravel FilterQ</a> under the hood, which allows you to write advanced logic like the above example, using comparison and logical operators.</p>

<p>A condition consists of three parts:</p>
<ul>
    <li><code>key</code></li>
    <li><code>operator</code></li>
    <li><code>value</code></li>
</ul>

<h5 id="operators">Operators</h5>
    <ul>
        <li><code>=</code> - equals</li>
        <li><code>!=</code> - not equals</li>
        <li><code>{`>`}</code> - greater than</li>
        <li><code>{`<`}</code> - less than</li>
        <li><code>{`>=`}</code> - greater than or equals</li>
        <li><code>{`<=`}</code> - less than or equals</li>
    </ul>

<h5 id="values">Values</h5>
    <ul>
        <li><code>null</code></li>
        <li>bool: <code>true</code> or <code>false</code></li>
        <li>string: <code>'hello'</code> or <code>hello</code></li>
        <ul>
            <li>Strings without quotes should match <code>[a-zA-Z_][a-zA-Z0-9_-]+</code> and cannot be <code>true</code>, <code>false</code>, or <code>null</code>.</li>
        </ul>

        <li>numbers: <code>250</code>, <code>-250</code>,<code> 2.5</code></li>
    </ul>

<h5 id="logical-operators">Logical Operators</h5>
<p>You can use Logical Operators to combine multiple conditions.</p>
<ul>
    <li><code>|</code> - OR</li>
    <li><code>&</code> - AND</li>
</ul>

<p>Please see the <a href="https://github.com/hyvor/laravel-filterq#filterq-expressions">FilterQ Expressions</a> documentation if you need more details.</p>

<h5 id="">Supported Keys for Filtering</h5>
<Table columns="1fr 2fr 1fr 1fr 1fr" hover>
    <TableRow head>
        <div>Endpoint</div>
        <div>Key</div>
        <div>Supported Operators</div>
        <div>Value Type</div>
        <div>Description</div>
    </TableRow>

    <TableRow>
        <div><code>/posts</code></div>
        <div><code>id</code></div>
        <div>all</div>
        <div><code>integer</code></div>
        <div></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>published_at</code></div>
        <div>all</div>
        <div><code>date</code></div>
        <div>See <a href="/docs/api-data#filter-date">Date</a></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>updated_at</code></div>
        <div>all</div>
        <div><code>date</code></div>
        <div>See <a href="/docs/api-data#filter-date">Date</a></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>created_at</code></div>
        <div>all</div>
        <div><code>date</code></div>
        <div>See <a href="/docs/api-data#filter-date">Date</a></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>is_featured</code></div>
        <div><code>=</code>, <code>!=</code></div>
        <div><code>boolean</code></div>
        <div></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>slug</code></div>
        <div><code>=</code>, <code>!=</code></div>
        <div><code>string</code></div>
        <div></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>featured_image_url</code></div>
        <div><code>=</code>, <code>!=</code></div>
        <div><code>null</code></div>
        <div>only to check if null or not</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>canonical_url</code></div>
        <div><code>=</code>, <code>!=</code></div>
        <div><code>string</code></div>
        <div>only to check if null or not</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>words</code></div>
        <div>all</div>
        <div><code>integer</code></div>
        <div></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>tag.id</code></div>
        <div>all</div>
        <div><code>integer</code></div>
        <div>Matches the id of the tags of the post</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>tag.slug</code></div>
        <div><code>=</code>, <code>!=</code></div>
        <div><code>string</code></div>
        <div>Matches the slug of the tags of the post</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>author.id</code></div>
        <div>all</div>
        <div><code>integer</code></div>
        <div>Similar to tag.id</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>author.slug</code></div>
        <div><code>=</code>, <code>!=</code></div>
        <div><code>string</code></div>
        <div>Similar to tag.slug</div>
    </TableRow>

    <TableRow>
        <div><code>/tags</code> and <code>/authors</code></div>
        <div><code>id</code></div>
        <div>all</div>
        <div><code>integer</code></div>
        <div></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>slug</code></div>
        <div><code>=</code>, <code>!=</code></div>
        <div><code>string</code></div>
        <div></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>post_count</code></div>
        <div>all</div>
        <div><code>integer</code></div>
        <div></div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>created_at</code></div>
        <div>all</div>
        <div><code>date</code></div>
        <div>See <a href="/docs/api-data#filter-date">Date</a></div>
    </TableRow>
</Table>

<h5 id="filter-date">Date Values</h5>
<p>Here are some valid values for date keys.</p>
<ul>
    <li><code>'2022-01-01'</code></li>
    <li><code>'yesterday'</code></li>
    <li><code>'first day of this year'</code></li>
    <li><code>'last day of next month'</code></li>
    <li><code>'+1 day'</code></li>
    <li><code>'-1 week'</code></li>
    <li><code>'next Thursday'</code></li>
    <li><code>1639655890</code> - UNIX Timestamp</li>
</ul>

<p>For example: In <code>/posts</code> endpoint, you may use <code>{`published_at>'-7 days'`}</code> to get posts published in the last 7 days.</p>

<h5 id="filer-examples">Filtering Examples</h5>

<Callout type="info">
    <p>Note that when calling the API via HTTP, the filter value should be URL-encoded.</p>
</Callout>

<p>To get posts authored by Alex:</p>

<CodeBlock language="ts" code={`
// filter
author.slug=alex

// URL-encoded
/posts?filter=author.slug%3Dalex
`} />

<p>To get featured posts:</p>

<CodeBlock language="ts" code={`
// filter
is_featured=true

// URL-encoded
/posts?filter=is_featured%3Dtrue
`} />

<p>To get posts with either the tag <code>audio</code> or <code>video</code>:</p>
<CodeBlock language="ts" code={`
// filter
tag.slug=audio|tag.slug=video

// URL-encoded
/posts?filter=tag.slug%3Daudio%7Ctag.slug%3Dvideo
`} />

<p>To get tags that have at least 5 posts:</p>
<CodeBlock language="ts" code={`
// filter
posts_count>=5

// URL-encoded
/tags?filter=posts_count%3E%3D5
`} />

<p>To get authors who are added after January 1st 2020:</p>
<CodeBlock language="ts" code={`
// filter
created_at>='2020-01-01'

// URL-encoded
/authors?filter=created_at%3E%3D%272020-01-01%27
`} />

<p>To get posts published in the last 7 days.</p>
<CodeBlock language="ts" code={`
// filter
published_at>'-7 days'

// URL-encoded
/posts?filter=published_at%3E%27-7%20days%27
`} />

<h4 id="sort">5. <code>sort</code> param</h4>
<p>Here's a list of supported sort values. You can combine multiple as comma-separated-values, which then will be executed in its order, similar to <code>ORDER BY</code> in SQL.</p>

<Table columns="2fr 2fr 2fr" hover>
    <TableRow head>
        <div>Endpoint</div>
        <div>Sort</div>
        <div>Description</div>
    </TableRow>

    <TableRow>
        <div><code>/posts</code> Default <code>published_at DESC</code></div>
        <div><code>published_at</code></div>
        <div>Post publish time</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>created_at</code></div>
        <div>Post create time</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>updated_at</code></div>
        <div>Post last update time</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>id</code></div>
        <div>Post ID</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>is_featured</code></div>
        <div>Think of this as an integer, 1 for true and 0 for false</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>title</code></div>
        <div>alphabetically</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>words</code></div>
        <div></div>  
    </TableRow>

    <TableRow>
        <div><code>/tags</code> and <code>/authors</code> Default <code>posts_count DESC</code></div>
        <div><code>post_count</code></div>
        <div>number of posts of the tag/author</div>
    </TableRow>

    <TableRow>
        <div></div>
        <div><code>created_at</code></div>
        <div></div>
    </TableRow>
</Table>

<p>The default sort method is <code>DESC</code>. Here are some examples for the sort param.</p>

<ul>
    <li><code>published_at</code> - sorted by published_at in descending order</li>
    <li><code>published_at ASC</code> - sorted by published_at in ascending order</li>
    <li><code>is_featured DESC</code>, <code>published_at DESC</code> - featured posts first, then ordered by publish time in descending order. <code>DESC</code> is optional. <code>is_featured</code>, <code>published_at</code> is identical with the former.</li>
</ul>

<h4 id="keys">6. <code>keys</code> param</h4>
<p>The <code>keys</code> can be used to include or exclude keys from the Objects, similar to GraphQL. All endpoints support the <code>keys</code> param.</p>

<p>If you call the <code>/posts</code> endpoint, with <code>keys=id,content</code>, the post objects will only contain those two keys.</p>

<CodeBlock language="ts" code={`
{
    "id": 1000,
    "content": "<p></p>"
}
`} />

<p>Use <code>!</code> at the start to exclude tags. For example, <code>keys=!content,description</code> will exclude <code>content</code> and <code>description</code> from the Post object and all other keys will be included.</p>

<p>Let's say you only want to get the post ID and tag ID of the posts. Use <code>keys=id,tags.id</code>. You will get objects like this.</p>

<CodeBlock language="ts" code={`
{
    "id": 1000,
    "tags": [
        {
            "id": 2000
        }
    ]
}
`} />

<h2 id="objects">Objects</h2>
<Callout type="info">
    <p>All timestamps are in <a href="https://www.unixtimestamp.com/">Unix Timestamp</a> format (integer).</p>
</Callout>

<h3 id="post-object">Post Object</h3>
<CodeBlock language="ts" code={`
{
    "id": 1000,

    "created_at": 1639655890,
    "updated_at": 1639655890,
    "published_at": 1639665890,

    "is_featured": false,
    "is_page": false,
    "slug": "hello-world",
    "content": "<p></p>",
    "title": "Hello World",
    "description": "This is a hello world page",
    "url": "https://subdomain.hyvorblogs.io/hello-world",
    "featured_image_url": "https://example.com/image.png",
    "canonical_url": null,
    "words": 500,
    "code_head": "",
    "code_foot": "",

    "language": language object,
    "variants": [ variant objects ],

    "tags": [ tag objects ],
    "authors": [ author objects ]
}
`} />

<Table columns="2fr 2fr 3fr" hover>
    <TableRow head>
        <div>Key</div>
        <div>Type</div>
        <div>Description</div>
    </TableRow>

    <TableRow>
        <div><code>id</code></div>
        <div><code>integer</code></div>
        <div>A unique ID for the post</div>
    </TableRow>

    <TableRow>
        <div><code>created_at</code></div>
        <div><code>integer</code></div>
        <div>Time when the post was created</div>
    </TableRow>

    <TableRow>
        <div><code>updated_at</code></div>
        <div><code>integer</code></div>
        <div>The time the post or its meta data was updated</div>
    </TableRow>

    <TableRow>
        <div><code>published_at</code></div>
        <div><code>integer</code></div>
        <div>Publish time of the post</div>
    </TableRow>

    <TableRow>
        <div><code>is_featured</code></div>
        <div><code>boolean</code></div>
        <div>Whether the post is featured. There can be multiple featured posts on a blog</div>
    </TableRow>

    <TableRow>
        <div><code>is_page</code></div>
        <div><code>boolean</code></div>
        <div>Whether it is a page. See <a href="docs/writing#posts-pages">Posts & Pages</a></div>
    </TableRow>

    <TableRow>
        <div><code>slug</code></div>
        <div><code>string</code></div>
        <div>The URL slug of the post</div>
    </TableRow>

    <TableRow>
        <div><code>url</code></div>
        <div><code>string</code></div>
        <div>The absolute URL of the post, generated based on where the blog is hosted.</div>
    </TableRow>

    <TableRow>
        <div><code>content</code></div>
        <div><code>string</code></div>
        <div>The post content in HTML. See <a href="/docs/writing">Content & The Editor</a> to see supported HTML tags</div>
    </TableRow>

    <TableRow>
        <div><code>title</code></div>
        <div><code>string</code></div>
        <div>The title of the post, max length 256</div>
    </TableRow>

    <TableRow>
        <div><code>description</code></div>
        <div><code>string | null</code></div>
        <div>The description (excerpt) of post, max length 350, null if not set</div>
    </TableRow>

    <TableRow>
        <div><code>featured_image_url</code></div>
        <div><code>string | null</code></div>
        <div>The absolute URL of the featured image. null if not set</div>
    </TableRow>

    <TableRow>
        <div><code>canonical_url</code></div>
        <div><code>string | null</code></div>
        <div>An absolute URL or null. Canonical URL is set by the author if the post was published somewhere else.</div>
    </TableRow>

    <TableRow>
        <div><code>words</code></div>
        <div><code>integer</code></div>
        <div>Number of words in the content</div>
    </TableRow>

    <TableRow>
        <div><code>code_head</code></div>
        <div><code>string</code></div>
        <div><a href="/docs/custom-code">Custom code</a> to add before <code>{`</head>`}</code> . An empty string if nothing is set.</div>
    </TableRow>

    <TableRow>
        <div><code>code_foot</code></div>
        <div><code>string</code></div>
        <div><a href="/docs/custom-code">Custom code</a> to add before <code>{`</body>`}</code> . An empty string if nothing is set.</div>
    </TableRow>

    <TableRow>
        <div><code>language</code></div>
        <div><code>object</code></div>
        <div>A <a href="/docs/api-data#language-object">Language object</a></div>
    </TableRow>

    <TableRow>
        <div><code>variants</code></div>
        <div><code>array</code></div>
        <div>An array of <a href="/docs/api-data#variant-object">Variant objects</a></div>
    </TableRow>

    <TableRow>
        <div><code>tags</code></div>
        <div><code>array</code></div>
        <div>An array of <a href="/docs/api-data#tag-object">Tag objects</a>. The primary tag is the index 0</div>
    </TableRow>

    <TableRow>
        <div><code>authors</code></div>
        <div><code>array</code></div>
        <div>An array of <a href="/docs/api-data#author-object">Author objects</a>. The primary author is the index 0</div>
    </TableRow>
</Table>

<Callout type="info">
    <p>In posts, <b>id</b> attribute is globally unique within Hyvor Blogs. The <b>slug</b> attribute is unique within the blog.</p>
</Callout>

<h3 id="tag-object">Tag Object</h3>

<CodeBlock language="ts" code={`
{
    "id": 2000,
    "created_at": 1639655890,
    "name": "Hello World",
    "description": "Saying hello to the world",
    "slug": "hello-world",
    "url": "https://subdomain.hyvorblogs.io/tag/hello-world",
    "posts_count": 20,
    "code_head": null,
    "code_foot": "<script></script>",

    "language": language object,
    "variants": [ variant objects ],
}
`} />

<Table columns="2fr 2fr 3fr" hover>
    <TableRow head>
        <div>Key</div>
        <div>Type</div>
        <div>Description</div>
    </TableRow>

    <TableRow>
        <div><code>id</code></div>
        <div><code>integer</code></div>
        <div>A unique ID for the tag</div>
    </TableRow>

    <TableRow>
        <div><code>created_at</code></div>
        <div><code>integer</code></div>
        <div>The time the tag was created</div>
    </TableRow>

    <TableRow>
        <div><code>name</code></div>
        <div><code>string</code></div>
        <div>Name (or title) of the tag</div>
    </TableRow>

    <TableRow>
        <div><code>description</code></div>
        <div><code>string | null</code></div>
        <div>Description of the tag</div>
    </TableRow>

    <TableRow>
        <div><code>slug</code></div>
        <div><code>string</code></div>
        <div>URL slug of the tag (full default path will be <code>{`/tag/{slug}`}</code>)</div>
    </TableRow>

    <TableRow>
        <div><code>url</code></div>
        <div><code>string</code></div>
        <div></div>
    </TableRow>

    <TableRow>
        <div><code>posts_count</code></div>
        <div><code>integer</code></div>
        <div>Number of posts of the tag</div>
    </TableRow>

    <TableRow>
        <div><code>language</code></div>
        <div><code>object</code></div>
        <div>A <a href="/docs/api-data#language-object">Language object</a></div>
    </TableRow>

    <TableRow>
        <div><code>variants</code></div>
        <div><code>array</code></div>
        <div>An array of <a href="/docs/api-data#variant-object">Variant objects</a></div>
    </TableRow>
</Table>

<h3 id="author-object">Author Object</h3>
<Callout type="info">
    <p>Author is a <a href="https://blogs.hyvor.com/docs/users">user</a> who has written at least one post</p>
</Callout>

<CodeBlock language="ts" code={`
{
    "id": 3000,
    "created_at": 1639655890,
    "slug": "blogger",
    "url": "https://subdomain.hyvorblogs.io/author/blogger",
    "name": "Blogger",
    "picture_url": "https://example.com/image.png",
    "bio": "I am a blogger",
    "website_url": "https://example.com",
    "location": "France",
    "social": social media object,
    "posts_count": 32,

    "language": language object,
    "variants": [ variant objects ],
}
`} />

<Table columns="2fr 2fr 3fr" hover>
    <TableRow head>
        <div>Key</div>
        <div>Type</div>
        <div>Description</div>
    </TableRow>

    <TableRow>
        <div><code>id</code></div>
        <div><code>integer</code></div>
        <div>A unique ID for the author</div>
    </TableRow>

    <TableRow>
        <div><code>created_at</code></div>
        <div><code>integer</code></div>
        <div>The time the author was created</div>
    </TableRow>

    <TableRow>
        <div><code>slug</code></div>
        <div><code>string</code></div>
        <div>URL slug of the author (full default path will be <code>{`/author/{slug}`}</code>)</div>
    </TableRow>

    <TableRow>
        <div><code>url</code></div>
        <div><code>string</code></div>
        <div>	Full URL of the user</div>
    </TableRow>

    <TableRow>
        <div><code>name</code></div>
        <div><code>string</code></div>
        <div>Author's name. Max length 50</div>
    </TableRow>

    <TableRow>
        <div><code>picture_url</code></div>
        <div><code>string | null</code></div>
        <div>The absolute URL of the author's picture. Usually, a small squared image</div>
    </TableRow>

    <TableRow>
        <div><code>bio</code></div>
        <div><code>string | null</code></div>
        <div>Author's bio. Max length 256</div>
    </TableRow>

    <TableRow>
        <div><code>website_url</code></div>
        <div><code>string | null</code></div>
        <div>The absolute URL of the author's website</div>
    </TableRow>

    <TableRow>
        <div><code>location</code></div>
        <div><code>string | null</code></div>
        <div>Author's location. Max length 30</div>
    </TableRow>

    <TableRow>
        <div><code>social</code></div>
        <div><code>object</code></div>
        <div>A <a href="/docs/api-data#social-media-object">Social Media object</a></div>
    </TableRow>

    <TableRow>
        <div><code>posts_count</code></div>
        <div><code>integer</code></div>
        <div>Number of posts written by the author</div>
    </TableRow>

    <TableRow>
        <div><code>language</code></div>
        <div><code>object</code></div>
        <div>A <a href="/docs/api-data#language-object">Language object</a></div>
    </TableRow>

    <TableRow>
        <div><code>variants</code></div>
        <div><code>array</code></div>
        <div>An array of <a href="/docs/api-data#variant-object">Variant objects</a></div>
    </TableRow>

</Table>

<h3 id="blog-object">Blog Object</h3>

<CodeBlock language="ts" code={`
{
    "subdomain": "alex",
    "name": "My Blog", 
    "description": "This is my blog hosted on Hyvor Blogs",
    "logo_url": "https://blog.hyvorblogs.io/media/logo.png",
    "icon_url": "https://blog.hyvorblogs.io/media/icon.png",
    "cover_url": "https://blog.hyvorblogs.io/media/cover.png",
    "url": "https://blog.hyvorblogs.io",
    "social": social media object,
    "nav_header": [
        {
            "name": "Home",
            "url": "/"
        },
        {
            "name": "About",
            "url": "/about"
        }
    ],
    "nav_footer": [
        {
            "name": "Privacy",
            "url": "/privacy"
        }
    ],

    "languages": [ language objects ],

    "code_head": "",
    "code_foot": "",

    "posts_count": 200,

    // the following are blog settings
    // which are used for generating header code and color themes
    "seo_indexing": true,
    "color_modes": "light",
    "color_mode_default": "light"
}
`} />

<Table columns="2fr 2fr 3fr" hover>
    <TableRow head>
        <div>Key</div>
        <div>Type</div>
        <div>Description</div>
    </TableRow>

    <TableRow>
        <div><code>subdomain</code></div>
        <div><code>string</code></div>
        <div>Subdomain of the blog</div>
    </TableRow>

    <TableRow>
        <div><code>name</code></div>
        <div><code>string</code></div>
        <div>Name/title of the blog</div>
    </TableRow>

    <TableRow>
        <div><code>description</code></div>
        <div><code>string</code></div>
        <div>A short description of the blog (256 max)</div>
    </TableRow>

    <TableRow>
        <div><code>logo_url</code></div>
        <div><code>string | null</code></div>
        <div>The absolute URL of the blog icon. Usually, a small square image</div>
    </TableRow>

    <TableRow>
        <div><code>cover_url</code></div>
        <div><code>string | null</code></div>
        <div>The absolute URL of the featured/cover image</div>
    </TableRow>

    <TableRow>
        <div><code>url</code></div>
        <div><code>string | null</code></div>
        <div>Absolute URL of the blog for the current language</div>
    </TableRow>

    <TableRow>
        <div><code>base_url</code></div>
        <div><code>string</code></div>
        <div>Absolute URL of the blog.</div>
    </TableRow>

    <TableRow>
        <div><code>social</code></div>
        <div><code>object</code></div>
        <div>A <a href="/docs/api-data#social-media-object">Social Media object</a></div>
    </TableRow>

    <TableRow>
        <div><code>nav_header</code>, <code>nav_footer</code></div>
        <div><code>array of objects</code></div>
        <div>Navigation links for the blog header and the footer.</div>
    </TableRow>

    <TableRow>
        <div><code>languages</code></div>
        <div><code>array of objects</code></div>
        <div>All available languages of the blog. See <a href="/docs/api-data#language-object">Language object</a></div>
    </TableRow>

    <TableRow>
        <div><code>code_head</code>, <code>code_foot</code></div>
        <div><code>string</code></div>
        <div>Custom HTML code for before <code>{`</head>`}</code>, and <code>{`</body>`}</code> for all pages.</div>
    </TableRow>

    <TableRow>
        <div><code>posts_count</code></div>
        <div><code>integer</code></div>
        <div>Total published posts</div>
    </TableRow>
</Table>

<h3 id="language-object">Language Object</h3>

<CodeBlock language="ts" code={`
{
    "id": 1000,
    "code": "en",
    "name": "English",
    "is_primary": true,
    "direction": "ltr"
}
`} />

<Table columns="2fr 2fr 3fr" hover>
    <TableRow head>
        <div>Key</div>
        <div>Type</div>
        <div>Description</div>
    </TableRow>

    <TableRow>
        <div><code>id</code></div>
        <div><code>integer</code></div>
        <div>A unique ID for the language</div>
    </TableRow>

    <TableRow>
        <div><code>code</code></div>
        <div><code>string</code></div>
        <div>Language code</div>
    </TableRow>

    <TableRow>
        <div><code>name</code></div>
        <div><code>string</code></div>
        <div>Language name</div>
    </TableRow>

    <TableRow>
        <div><code>is_primary</code></div>
        <div><code>boolean</code></div>
        <div>Whether it is the primary language of the blog</div>
    </TableRow>

    <TableRow>
        <div><code>direction</code></div>
        <div><code>string</code></div>
        <div>Text direction. <code>ltr</code> or <code>rtl</code></div>
    </TableRow>
</Table>

<h3 id="variant-object">Variant Object</h3>
<p>A variant object contains data of a language variant of a post, tag, or an author.</p>

<CodeBlock language="ts" code={`
{
    "language": {
        "id": 1001,
        "code": "fr",
        "name": "French",
        "is_primary": false,
        "direction": "ltr"
    },
    "url": "https://subdomain.hyvorblogs.io/fr/hello-world"
}
`} />

<Table columns="2fr 2fr 3fr" hover>
    <TableRow head>
        <div>Key</div>
        <div>Type</div>
        <div>Description</div>
    </TableRow>

    <TableRow>
        <div><code>language</code></div>
        <div><code>object</code></div>
        <div>A <a href="/docs/api-data#language-object">Language object</a></div>
    </TableRow>

    <TableRow>
        <div><code>url</code></div>
        <div><code>string</code></div>
        <div>URL of the variant</div>
    </TableRow>
</Table>

<h3 id="pagination-object">Pagination Object</h3>

<p>A pagination object is included in all multi-object endpoints (<code>/posts</code>, <code>/authors</code>, <code>/tags</code>).</p>

<CodeBlock language="ts" code={`
{
    "total": 100,
    "pages": 10,
    "limit": 5,
    "page": 1,
    "page_prev": null,
    "page_next": 2,
}
`} />

<Table columns="2fr 2fr 3fr" hover>
    <TableRow head>
        <div>Key</div>
        <div>Type</div>
        <div>Description</div>
    </TableRow>

    <TableRow>
        <div><code>total</code></div>
        <div><code>integer</code></div>
        <div>The total number of results possible with the current filters</div>
    </TableRow>

    <TableRow>
        <div><code>pages</code></div>
        <div><code>integer</code></div>
        <div>The number of the total pagination pages based on the limit you set. <code>{`pages = round_to_upper(total/limit)`}</code></div>
    </TableRow>

    <TableRow>
        <div><code>limit</code></div>
        <div><code>integer</code></div>
        <div>Current limit</div>
    </TableRow>

    <TableRow>
        <div><code>page</code></div>
        <div><code>integer</code></div>
        <div>Current page</div>
    </TableRow>

    <TableRow>
        <div><code>page_prev</code></div>
        <div><code>integer</code> or <code>string</code></div>
        <div>Previous page number (<code>null</code> if no previous pages)</div>
    </TableRow>

    <TableRow>
        <div><code>page_next</code></div>
        <div><code>integer</code> or <code>string</code></div>
        <div>Next page number (<code>null</code> if no next pages)</div>
    </TableRow>
</Table>

<h3 id="social-media-object">Social Media Object</h3>
<CodeBlock language="ts" code={`
{
    "facebook": null,
    "twitter": "https://twitter.com/HyvorBlogs",
    "linkedin": "https://www.linkedin.com/company/30240435",
    "youtube": null,
    "instagram": null,
    "github": "https://github.com/hyvor",
    "tiktok": null
}
`} />

<h2 id="error-handling">Error Handling</h2>

<p>In case of an error, the HTTP status code will be a non-200 status code.</p>

    <p>For 4xx errors, the response will be a JSON object.</p>

<CodeBlock language="ts" code={`
{
    "error": "ID is required",
    "error_code": "422"
}
`} />

<p>These HTTP codes are possible:</p>

<ul>
    <li><cb>404 Not Found</cb> - Resource not found</li>
    <ul>
        <li>404 can be returned in a single-object endpoints when the object is not found</li>
        <li>Make sure ID/slug (and language for posts) is correct</li>
    </ul>
    <li><b>422 Unprocessable Entity</b> - Invalid input</li>
    <ul>
        <li>Check the query params</li>
        <li>You can find more details in the JSON output of the error</li>
    </ul>
</ul>

<p>5xx errors means something is wrong on our side. Check our <a href="https://status.hyvor.com/">status page</a> for any downtimes. If the issue persists, <a href="/docs/support">contact us</a>.</p>

<h2 id="pages">Pages</h2>

<p>We do not have separate endpoints to fetch <a href="/docs/writing#posts-pages">Pages</a>.</p>
<ul>
    <li> To get a single page, call the <code>/post</code> endpoint with the page ID or slug.</li>
    <li>To get multiple pages, call the <code>/posts</code> endpoint with <code>?pages=true</code> param.</li>
    <li><code>/posts/search</code> does not support searching pages.</li>
</ul>

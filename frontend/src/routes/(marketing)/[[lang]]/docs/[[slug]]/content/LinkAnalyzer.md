<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Link Analyzer

The link analysis tool in the post editor analyzes the status of the links in your post as you write. It will show you a warning if there are any broken, risky, or redirect links in your post. It also shows you the [type of each link](#link-types).

<DocsImage src="/images/docs/writing/link-analysis.png" alt="Link Analysis" width={400} />

<h2 id="link-types">Link Types</h2>

Hyvor Blogs categorizes links into the following types.

<Table columns="2fr 3fr" hover>
	<TableRow head>
		<div>Link Type</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>internal-blog</code></div>
    	<div>Links to other posts/pages in your blog</div>
    </TableRow>

    <TableRow>
    	<div><code>internal-domain</code></div>
    	<div>Links to the same domain as your blog, but not to your blog</div>
    </TableRow>

    <TableRow>
    	<div><code>internal-root-domain</code></div>
    	<div>Links to any domain of the root domain, but not to your blog's domain</div>
    </TableRow>

    <TableRow>
    	<div><code>external</code></div>
    	<div>Links to other domains</div>
    </TableRow>

    <TableRow>
    	<div><code>mail</code></div>
    	<div>Mailto links (starts with <code>mailto:</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>tel</code></div>
    	<div>Tel links (starts with <code>tel:</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>anchor</code></div>
    	<div>Links to anchors in the same page (starts with <code>#</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>other</code></div>
    	<div>Other links (<code>ftp:</code>, <code>data:</code>, javascript, etc.)</div>
    </TableRow>

</Table>

Go to **Console → Tools → Link Analysis** to

- see an overview of all links in your blog
- view results of bi-weekly link analysis
- change email report settings

<h3 id="link-analysis-accuracy">Link Analysis Accuracy</h3>

Our link analyzer is simple: it sends HTTP requests via curl to check the status of the links. This approach allows for a fast and accurate analysis. However, some servers and firewalls may block these requests, which may result in false positives. If you find a link that is marked as broken but is actually working, you can click the ignore button to ignore the link in future analyses.

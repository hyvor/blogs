<script lang="ts">
	import { CodeBlock, Callout } from '@hyvor/design/components';
</script>

<h1>Scripts</h1>

<p>
	Unlike SCSS support, Hyvor Blogs does not support pre-processing JS (for Typescript or Modules
	support). So, you have to write Javascript that browser understands directly. You can create
	Javascript files in the <code>/assets</code> folder and link to them in the template.
</p>

<CodeBlock
	code={`
       <` +
		`script src="{{ 'script.js' | asset_url }}"></script>
    `}
/>

<p>Use <code>async</code> for non-essential scripts.</p>

<CodeBlock
	code={`
       <` +
		`script src="{{ 'non-essential.js' | asset_url }}"></script>
    `}
/>

<p>If possible, try to write inline Javascript to avoid HTTP requests completely.</p>

<CodeBlock
	code={`
      <` +
		`script>
         // my js here
      </script>
    `}
/>

<h2 id="flashload-safe">Flashload Safe</h2>
<p>
	<a href="https://github.com/hyvor/flashload">Flashload</a> does magic, but make sure you
	understand how it works to avoid common pitfalls. Let's say you are navigating from
	<code>/</code>
	to <code>/page</code>. Flashload prevents the browser reload and loads the page by itself and
	replaces <b>only the</b> <code>{`<body>`}</code>. It means,
</p>

<ul>
	<li>
		CSS in <code>{`<head>`}</code> is only loaded once. That is why we have a single
		<code>styles.css</code> stylesheet that contains all styles of the blog.
	</li>
	<li>
		<code>{`<script>`}</code>s in <code>{`<head>`}</code> will only run in the first page load, and
		not when navigating.
	</li>
	<li>
		<code>{`<script>`}</code>s in <code>{`<body>`}</code> will load/run on each navigation,
		unless they have a <b>data-flashload-skip-script</b> attribute.
	</li>
</ul>

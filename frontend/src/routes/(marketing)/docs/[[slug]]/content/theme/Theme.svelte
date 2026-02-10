<script>
	import { Callout } from '@hyvor/design/components';
</script>

<h1>Theme</h1>

<p>
	Hyvor Blogs comes with a few pre-built themes (see <a href="/themes">Themes</a>). When you
	create a blog, the default theme, <b>Hello</b>, will be copied to your blog. You can change the
	theme or edit theme files to customize your blog.
</p>

<ul>
	<li><a href="/docs/theme#changing">Changing Theme</a></li>
	<li><a href="/docs/theme#editing">Editing Theme Files</a></li>
</ul>

<Callout type="info">
	<p>
		If you want to develop a custom theme, visit the <a href="/docs/themes-overview"
			>Theme Development</a
		> page.
	</p>
</Callout>

<h2 id="changing">Changing Theme</h2>
<p>To change the theme of your blog,</p>
<ul>
	<li>Visit the <b>Theme</b> section in the <a href="/console">Hyvor Blogs Console</a>.</li>
	<li>Click the <b>Change</b> button.</li>
	<li>Select a theme from the list.</li>
</ul>

<Callout type="warning">
	<p>
		<b>Important</b>: Changing the theme will overwrite all the theme files in your blog. If you
		have made any changes to the theme files, you will lose them.
	</p>
</Callout>

<h2 id="editing">Editing Theme Files</h2>
<p>
	You can also edit the theme files of your blog to customize it. To edit theme files, go to the <b
		>Theme</b
	>
	section in the <a href="/console">Hyvor Blogs Console</a>. You will see all the theme files
	listed. Click on a file to edit it.
</p>

<!---image-->

<p>
	<code>config.yaml</code> has all theme configurations such as fonts, colors, and other settings.
	All template files are in the <code>templates</code> directory.
</p>

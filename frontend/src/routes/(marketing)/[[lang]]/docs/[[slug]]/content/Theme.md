<script>
	import { Callout } from '@hyvor/design/components';
</script>

# Theme

Hyvor Blogs comes with a few pre-built themes (see [Themes](/themes)). When you create a blog, the default theme, **Hello**, will be copied to your blog. You can change the theme or edit theme files to customize your blog.

- [Changing Theme](/docs/theme#changing)
- [Editing Theme Files](/docs/theme#editing)

<Callout type="info">
	<p>
		If you want to develop a custom theme, visit the <a href="/docs/themes-overview"
			>Theme Development</a
		> page.
	</p>
</Callout>

<h2 id="changing">Changing Theme</h2>

To change the theme of your blog,

- Visit the **Theme** section in the [Hyvor Blogs Console](/console).
- Click the **Change** button.
- Select a theme from the list.

<Callout type="warning">
	<p>
		<b>Important</b>: Changing the theme will overwrite all the theme files in your blog. If you
		have made any changes to the theme files, you will lose them.
	</p>
</Callout>

<h2 id="editing">Editing Theme Files</h2>

You can also edit the theme files of your blog to customize it. To edit theme files, go to the **Theme** section in the [Hyvor Blogs Console](/console). You will see all the theme files listed. Click on a file to edit it.

<!---image-->

`config.yaml` has all theme configurations such as fonts, colors, and other settings. All template files are in the `templates` directory.

# Scripts

Unlike SCSS support, Hyvor Blogs does not support pre-processing JS (for Typescript or Modules support). So, you have to write Javascript that browser understands directly. You can create Javascript files in the `/assets` folder and link to them in the template.

```html
<script src="{{ 'script.js' | asset_url }}"></script>
```

Use `async` for non-essential scripts.

```html
<script src="{{ 'non-essential.js' | asset_url }}"></script>
```

If possible, try to write inline Javascript to avoid HTTP requests completely.

```html
<script>
	// my js here
</script>
```

<h2 id="flashload-safe">Flashload Safe</h2>

[Flashload](https://github.com/hyvor/flashload) does magic, but make sure you understand how it works to avoid common pitfalls. Let's say you are navigating from `/` to `/page`. Flashload prevents the browser reload and loads the page by itself and replaces **only the** `<body>`. It means,

- CSS in `<head>` is only loaded once. That is why we have a single `styles.css` stylesheet that contains all styles of the blog.
- `<script>`s in `<head>` will only run in the first page load, and not when navigating.
- `<script>`s in `<body>` will load/run on each navigation, unless they have a **data-flashload-skip-script** attribute.

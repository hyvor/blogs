# Syntax Highlighting

Hyvor Blogs has a powerful syntax highlighter with the following features:

* Line numbering
* Line highlighting
* Diff (+ and -)
* Line focusing

Most importantly, syntax highlighting is done at the time of rendering posts in our back-end. Therefore, it does not require any additional Javascript or CSS. To change syntax highlighting settings, go to **Console &rarr; Settings &rarr; Post Content**.

## Languages {#languages}

Our syntax highlighter supports {{language_number}} programming languages:

<div class="language-tags">
    <div>Supported Languages</div>
    {{language_tags}}
</div>

## Themes {#themes}

Hyvor Blogs supports {{themes_number}} VS Code themes.

<div class="language-tags themes">
    <div>Supported Themes</div>
    {{theme_tags}}
</div>

<p>
<button class="button small" onClick="togglePreview()" id="toggle-button">Show theme previews</button>
</p>

<div id="theme-previews" style="display:none">
    {{theme_previews}}
</div>

<script>
    function togglePreview() {
        var previews = document.getElementById("theme-previews");
        var button = document.getElementById("toggle-button");
        if (previews.style.display === 'block') {
            previews.style.display = 'none';
            button.innerHTML = 'Show theme previews';
        } else {
            previews.style.display = 'block';
            button.innerHTML = 'Hide theme previews';
        }

    }
</script>

## Adding Code Blocks to Your Post {#adding}

See [Code Block](writing#code-block) in Writing.

## Annotations {#annotations}

Annotations are used for highlighting, focusing, and numbering lines. You can add annotations to the code block in the Editor. Let's see some examples.

| Annotation        | Description |
|-------------------| --- |
| `h=1`             | Highlights the first line
| `h=1-5`           | Highlights line 1 to 5
| `h=1,5,6`         | Highlights line 1, 5, and 6
| `h=1-4,7`         | Highlights 1 to 4, and then 7
| `f=1`             | Focuses number 1 (Works exactly as highlight)
| `+=12`            | Highlights the 12th line in green (for Diff add)
| `-=20`            | Highlights the 20th line in red (for Diff remove)
| `renumber=4:21`   | Number of the 4th line will be changed to 21. The next line will have 22.
| `renumber=6:null` | Number of the 6th line will be hidden
| `h=1 +=12`        | You can have multiple space separated annotations
| `numbers=true`    | Enable line numbers (to override global settings)
| `numbers=false`   | Disable line numbers (to override global settings)

## Tips

* Under the hood, Hyvor Blogs use [Shiki](https://github.com/shikijs/shiki) for syntax highlighting. Therefore, we can support and VSCode-supported language or theme. If you want to add any, contact us.
* Colors for syntax comes from our side, but styles like padding, margins, space between lines, and font sizes comes from the [theme](theme) of your blog.


<!-- DISABLE PRISM -->
<script>
window.Prism = window.Prism || {};
window.Prism.manual = true;
</script>
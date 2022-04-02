# Syntax Highlighting


<!-- Syntax Highlighting: **Console &rarr; Settings &rarr; Languages** -->

Hyvor Blogs has a powerful syntax highlighter with the following features:

* Line numbering
* Line highlighting
* Diff (+ and -)
* Line focusing

Most importantly, syntax highlighting is done at the time of rendering posts in our back-end. Therefore, it does not require any additional Javascript or CSS.

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

There are two ways to add code blocks.

## Annotations {#annotations}

Annotations are used for highlighting and re-numbering. Let's see some examples.

| Annotation | Description |
| --- | --- |
| `highlight=1` | Highlights the first line
| `highlight=1-5` | Highlights line 1 to 5
| `highlight=1,5,6` | Highlights line 1, 5, and 6
| `highlight=1-4,7` | Highlights 1 to 4, and then 7
| `focus=1` | Focuses number 1 (Works exactly as highlight)
| `+=12` | Highlights the 12th line in green (Diff add)
| `-=20` | Highlights the 20th line in red (Diff remove)
| `renumber=4:21` | Number of the 4th line will be changed to 21. The next line will have 22.
| `renumber=6:null` | Number of the 6th line will be hidden
| `highlight=1 +=12` | You can have multiple space separated annotations
| `numbers=true` | Enable line numbers (to override global settings)
| `numbers=false` | Disable line numbers (to override global settings)

### Displaying Language, File Name, Copy/Download Buttons

It is out of our scope to provide these options by default because the position and appearance of those elements greatly depends on your blog theme. Some [themes](themes) may provide such functionalities, but not guaranteed. If you want to add such functionalities, you will need to write custom CSS and Javascript to do that.

## Tips

* Under the hood, Hyvor Blogs use [Shiki](https://github.com/shikijs/shiki) for syntax highlighting. Therefore, we can support and VSCode-supported language or theme. If you want to add any, contact us.
* This page describes how syntax highlighting works **in your blog**. The code editor in the Console does not work in the same way. It also only supports a limited number of languages.
* Colors for syntax comes from our side, but styles like padding, margins, space between lines, and font sizes comes from the [theme](themes) of your blog.


<!-- DISABLE PRISM -->
<script>
window.Prism = window.Prism || {};
window.Prism.manual = true;
</script>
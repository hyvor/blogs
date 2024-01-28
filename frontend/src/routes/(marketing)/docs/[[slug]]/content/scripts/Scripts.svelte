<script lang="ts">
    import { CodeBlock, Callout } from "@hyvor/design/components";
</script>

<h1>Scripts</h1>

    <p>Unlike SCSS support, Hyvor Blogs does not support pre-processing JS (for Typescript or Modules support). So, you have to write Javascript that browser understands directly. You can create Javascript files in the <code>/assets</code> folder and link to them in the template.</p>

    <CodeBlock code={`
       <` + `script src="{{ 'script.js' | asset_url }}"></script>
    `} />

    <p>Use <code>async</code> for non-essential scripts.</p>

    <CodeBlock code={`
       <` + `script src="{{ 'non-essential.js' | asset_url }}"></script>
    `} />

    <p>If possible, try to write inline Javascript to avoid HTTP requests completely.</p>

    <CodeBlock code={`
       <` + `script>
        // my js here
        </script>
    `} />

